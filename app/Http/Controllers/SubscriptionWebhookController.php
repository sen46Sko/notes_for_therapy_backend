<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use Google\Client as GoogleClient;
use Google\Service\AndroidPublisher as Google_Service_AndroidPublisher;

class SubscriptionWebhookController extends Controller
{
     public function handleApplePayWebhook(Request $request)
    {
        $signedPayload = $request->input('signedPayload');
        if (!$signedPayload) {
            return response()->json(['error' => 'Missing signedPayload'], 400);
        }

        try {
            $decodedPayload = $this->decodeAppleNotification($signedPayload);
            Log::info('Apple Pay Webhook received', $decodedPayload['data']['decodedTransactionInfo']);
            Log::info('Apple Pay Webhook received', $decodedPayload['data']['decodedRenewalInfo']);

            return $this->processAppleSubscriptionUpdate($decodedPayload);
        } catch (\Exception $e) {
            Log::error('Error processing Apple Pay webhook: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        }
    }

    private function decodeAppleNotification($signedPayload)
    {
        $parts = explode('.', $signedPayload);
        if (count($parts) !== 3) {
            throw new \Exception('Invalid JWS format');
        }

        $decodedPayload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        if (!$decodedPayload) {
            throw new \Exception('Invalid payload');
        }

        if (isset($decodedPayload['data']['signedTransactionInfo'])) {
            $decodedPayload['data']['decodedTransactionInfo'] = $this->decodeJWS($decodedPayload['data']['signedTransactionInfo']);
        }
        if (isset($decodedPayload['data']['signedRenewalInfo'])) {
            $decodedPayload['data']['decodedRenewalInfo'] = $this->decodeJWS($decodedPayload['data']['signedRenewalInfo']);
        }

        return $decodedPayload;
    }

    private function decodeJWS($jws)
    {
        $parts = explode('.', $jws);
        return json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
    }

    private function processAppleSubscriptionUpdate($decodedPayload)
    {
        $notificationType = $decodedPayload['notificationType'] ?? null;
        $subtype = $decodedPayload['subtype'] ?? null;
        $data = $decodedPayload['data'] ?? null;

        if (!$data || !isset($data['decodedTransactionInfo'])) {
            return response()->json(['error' => 'Missing required information'], 400);
        }

        $transactionInfo = $data['decodedTransactionInfo'];
        $renewalInfo = $data['decodedRenewalInfo'] ?? null;
        $userUuid = $transactionInfo['appAccountToken'] ?? null;
        $subscriptionId = $transactionInfo['originalTransactionId'] ?? null;

        // Get current subscription status before updating
        $currentStatus = $this->getCurrentSubscriptionStatus($userUuid, $subscriptionId);

        // Only update status if the notification type requires a status change
        $newStatus = $this->mapAppleStatusToInternal($notificationType, $subtype, $currentStatus);
        $expirationDate = $transactionInfo['expiresDate'] ?? null;

        // Extract trial period information
        $trialStart = null;
        $trialEnd = null;

        if (isset($transactionInfo['isTrialPeriod']) && $transactionInfo['isTrialPeriod']) {
            $trialStart = isset($transactionInfo['purchaseDate'])
                ? Carbon::createFromTimestamp($transactionInfo['purchaseDate'] / 1000)
                : Carbon::now();

            $trialEnd = isset($transactionInfo['expiresDate'])
                ? Carbon::createFromTimestamp($transactionInfo['expiresDate'] / 1000)
                : null;
        }

        return $this->updateSubscription(
            $userUuid,
            $subscriptionId,
            $newStatus,
            $expirationDate,
            'apple_pay',
            null,
            $trialStart,
            $trialEnd
        );
    }

    private function updateSubscription(
        $userUuid,
        $subscriptionId,
        $status,
        $expirationDate,
        $provider,
        $purchaseToken = null,
        $trialStart = null,
        $trialEnd = null
    ) {
        if (!$userUuid || !$subscriptionId || !$status) {
            return response()->json(['error' => 'Missing required information'], 400);
        }

        $user = User::where('uuid', $userUuid)->first();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Update user subscription status and trial information
        $user->subscription_status = $status;

        // Only update trial dates if they are provided and the user is starting a new trial
        if ($trialStart && $trialEnd && ($status === 'active' || $status === 'trial')) {
            // Only set trial dates if they haven't been set before or if starting a new trial
            if (!$user->trial_start || $status === 'trial') {
                $user->trial_start = $trialStart;
                $user->trial_end = $trialEnd;
            }
        }

        $user->save();

        Log::info("Subscription updated for user {$user->id}", [
            'status' => $status,
            'trial_start' => $trialStart,
            'trial_end' => $trialEnd
        ]);

        return response()->json(['message' => 'Subscription updated successfully']);
    }


    private function mapAppleStatusToInternal($notificationType, $subtype, $currentStatus)
    {
        // Events that set status to 'active'
        $activeEvents = [
            'SUBSCRIBED',                                          // Initial subscription or resubscribe
            'DID_RENEW',                                           // Successful renewal
            'OFFER_REDEEMED',                                      // Offer redemption
            'DID_CHANGE_RENEWAL_STATUS' => ['AUTO_RENEW_ENABLED']  // Re-enable auto-renewal
        ];

        // Events that set status to 'inactive'
        $inactiveEvents = [
            'EXPIRED' => [
                'VOLUNTARY',             // User cancelled
                'BILLING_RETRY',         // Failed to renew after retry period
                'PRICE_INCREASE',        // User didn't consent to price increase
                'PRODUCT_NOT_FOR_SALE'   // Product no longer available
            ],
            'GRACE_PERIOD_EXPIRED',     // Grace period ended without renewal
            'REFUND',                   // Refund processed
            'REVOKE'                    // Family sharing revoked
        ];

        // Check for cancellation event
        if ($notificationType === 'DID_CHANGE_RENEWAL_STATUS' && $subtype === 'AUTO_RENEW_DISABLED') {
            return 'canceled'; // User has canceled but still has access until period ends
        }

        // Check for events that set status to 'active'
        if (in_array($notificationType, $activeEvents) ||
            (isset($activeEvents[$notificationType]) &&
            in_array($subtype, $activeEvents[$notificationType]))) {
            return 'active';
        }

        // Check for events that set status to 'inactive'
        if (in_array($notificationType, $inactiveEvents) ||
            (isset($inactiveEvents[$notificationType]) &&
            in_array($subtype, $inactiveEvents[$notificationType]))) {
            return 'inactive';
        }

        // Special case for DID_FAIL_TO_RENEW
        if ($notificationType === 'DID_FAIL_TO_RENEW') {
            // If in grace period, keep active, otherwise set to inactive
            return ($subtype === 'GRACE_PERIOD') ? 'active' : 'inactive';
        }

        // For all other notification types, maintain current status
        return $currentStatus;
    }

    private function getCurrentSubscriptionStatus($userUuid, $subscriptionId)
    {
        // Implement this method to fetch the current subscription status from your database
        // Return the current status or a default value if not found
        return 'inactive'; // Default implementation - replace with actual database query
    }


    public function handleGooglePayWebhook(Request $request)
    {
        // Verify the request comes from Google
        if (!$this->verifyNotDuplicatedGoogleMessage($request)) {
            Log::error('Duplicated request error');
            return response()->json(['error' => 'Repeated message received, aborting'], 200);
        }

        $message = $request->input('message', []);
        $data = $message['data'] ?? null;

        if (!$data) {
            return response()->json(['error' => 'Missing notification data'], 400);
        }

        try {
            $decodedData = $this->decodeGoogleNotification($data);
            Log::info('Google Play Webhook received', ['payload' => $decodedData]);

            $response = $this->processGoogleSubscriptionUpdate($decodedData);
            $this->cacheMessage($request);
            return $response;
        } catch (\Exception $e) {
            Log::error('Error processing Google Play webhook: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        }
    }

    private function verifyNotDuplicatedGoogleMessage(Request $request)
    {
        try {
            $message = $request->input('message', []);
            $messageId = $message['messageId'] ?? '';

            // Prevent replay attacks
            if (Cache::has('google_webhook_' . $messageId)) {
                return false;
            }
            // Cache::put('google_webhook_' . $messageId, true, 3600); // Store for 1 hour

            return true;
        } catch (\Exception $e) {
            Log::error('Error verifying Google signature: ' . $e->getMessage());
            return false;
        }
    }

    private function cacheMessage(Request $request)
    {
        $message = $request->input('message', []);
        $messageId = $message['messageId'] ?? '';

        Cache::put('google_webhook_' . $messageId, true, 3600); // Store for 1 hour
    }

    private function decodeGoogleNotification($encodedData)
    {
        $decodedJson = base64_decode($encodedData);
        $decodedData = json_decode($decodedJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON in decoded data');
        }

        return $decodedData;
    }

    private function processGoogleSubscriptionUpdate($decodedData)
    {
        // Extract the notification data
        $subscriptionNotification = $decodedData['subscriptionNotification'] ?? null;

        if (!$subscriptionNotification) {
            return response()->json(['error' => 'Invalid notification format'], 400);
        }

        $purchaseToken = $subscriptionNotification['purchaseToken'] ?? null;
        $subscriptionId = $subscriptionNotification['subscriptionId'] ?? null;
        $notificationType = $subscriptionNotification['notificationType'] ?? null;

        if (!$purchaseToken || !$subscriptionId) {
            return response()->json(['error' => 'Missing required subscription information'], 400);
        }

        try {
            // Fetch detailed subscription info from Google Play API
            $subscriptionInfo = $this->getSubscriptionInfoFromGoogle(
                $purchaseToken,
                $subscriptionId
            );

            $userUuid = $subscriptionInfo['userUuid'] ?? null;
            $status = $this->mapGoogleStatusToInternal($notificationType);

            // Convert Google's millisecond timestamps to Carbon instances
            $expiryTime = isset($subscriptionInfo['expiryTimeMillis'])
                ? Carbon::createFromTimestampMs($subscriptionInfo['expiryTimeMillis'])
                : null;

            $trialStart = null;
            $trialEnd = null;

            // Check if this is a trial period
            if (isset($subscriptionInfo['paymentState']) && $subscriptionInfo['paymentState'] === 2) { // 2 = Free trial
                $trialStart = Carbon::createFromTimestampMs($subscriptionInfo['startTimeMillis']);
                $trialEnd = $expiryTime;
            }

            return $this->updateSubscription(
                $userUuid,
                $subscriptionId,
                $status,
                $expiryTime,
                'google_pay',
                $purchaseToken,
                $trialStart,
                $trialEnd
            );

        } catch (\Exception $e) {
            Log::error('Error processing Google subscription update: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to process subscription update'], 500);
        }
    }

    private function getSubscriptionInfoFromGoogle($purchaseToken, $subscriptionId)
    {
        $client = new GoogleClient();
        $client->setAuthConfig(storage_path(
            config('services.google_play.credentials')
        ));
        $client->addScope('https://www.googleapis.com/auth/androidpublisher');

        $androidPublisher = new Google_Service_AndroidPublisher($client);

        try {
            $subscription = $androidPublisher->purchases_subscriptions->get(
                config('services.google_play.package_name'),
                $subscriptionId,
                $purchaseToken
            );

            return [
                'userUuid' => $subscription->getObfuscatedExternalAccountId(),
                'expiryTimeMillis' => $subscription->getExpiryTimeMillis(),
                'paymentState' => $subscription->getPaymentState(),
                'startTimeMillis' => $subscription->getStartTimeMillis(),
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching subscription info from Google: ' . $e->getMessage());
            throw $e;
        }
    }

    private function mapGoogleStatusToInternal($notificationType)
    {
        $statusMap = [
            1 => 'active',    // SUBSCRIPTION_RECOVERED
            2 => 'active',    // SUBSCRIPTION_RENEWED
            3 => 'canceled',  // SUBSCRIPTION_CANCELED
            4 => 'active',    // SUBSCRIPTION_PURCHASED
            5 => 'on_hold',   // SUBSCRIPTION_ON_HOLD
            6 => 'active',    // SUBSCRIPTION_IN_GRACE_PERIOD
            7 => 'active',    // SUBSCRIPTION_RESTARTED
            8 => 'inactive',  // SUBSCRIPTION_PRICE_CHANGE_CONFIRMED
            9 => 'inactive',  // SUBSCRIPTION_DEFERRED
            10 => 'paused',   // SUBSCRIPTION_PAUSED
            11 => 'active',   // SUBSCRIPTION_PAUSE_SCHEDULE_CHANGED
            12 => 'inactive', // SUBSCRIPTION_REVOKED
            13 => 'expired',  // SUBSCRIPTION_EXPIRED
        ];

        return $statusMap[$notificationType] ?? 'inactive';
    }

    // private function getUserUuidFromPurchaseToken($purchaseToken)
    // {
    //     // Query your database to find the user UUID associated with this purchase token
    //     $subscription = Subscription::where('purchase_token', $purchaseToken)->first();
    //     return $subscription ? $subscription->user_uuid : null;
    // }
}
