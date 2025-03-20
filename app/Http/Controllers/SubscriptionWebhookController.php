<?php

namespace App\Http\Controllers;

use App\Enums\SystemActionType;
use App\Models\User;
use App\Models\Subscription;
use App\Services\SystemActionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use Google\Client as GoogleClient;
use Google\Service\AndroidPublisher as Google_Service_AndroidPublisher;
use Illuminate\Support\Facades\DB;

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

    private function getCurrentSubscriptionStatus($userUuid, $subscriptionId)
    {
        // First try to find the subscription in our new subscriptions table
        $subscription = Subscription::where('provider_subscription_id', $subscriptionId)->first();
        if ($subscription) {
            return $subscription->status;
        }

        // If not found, default to inactive
        return 'inactive';
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
        $userUuid = $transactionInfo['appAccountToken'] ?? null;
        $purchaseToken = $transactionInfo['originalTransactionId'] ?? null;
        $subscriptionId = $transactionInfo['productId'] ?? null;

        $currentStatus = $this->getCurrentSubscriptionStatus($userUuid, $subscriptionId);
        $newStatus = $this->mapAppleStatusToInternal($notificationType, $subtype, $currentStatus);

        // Convert Apple's timestamps
        $expirationDate = isset($transactionInfo['expiresDate'])
            ? $transactionInfo['expiresDate'] / 1000
            : null;

        $trialStart = null;
        $trialEnd = null;

        if (isset($transactionInfo['isTrialPeriod']) && $transactionInfo['isTrialPeriod']) {
            $trialStart = isset($transactionInfo['purchaseDate'])
                ? Carbon::createFromTimestampMs($transactionInfo['purchaseDate'])
                : Carbon::now();

            $trialEnd = $expirationDate
                ? Carbon::createFromTimestampMs($expirationDate * 1000)
                : null;
        }

        return $this->updateSubscription(
            $userUuid,
            $subscriptionId,
            $newStatus,
            $expirationDate,
            'apple_pay',
            $purchaseToken,
            $trialStart,
            $trialEnd
        );
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

            if (Cache::has('google_webhook_' . $messageId)) {
                return false;
            }

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
            $subscriptionInfo = $this->getSubscriptionInfoFromGoogle(
                $purchaseToken,
                $subscriptionId
            );

            $userUuid = $subscriptionInfo['userUuid'] ?? null;
            $status = $this->mapGoogleStatusToInternal($notificationType);

            // Convert Google's millisecond timestamps
            $expiryTime = $subscriptionInfo['expiryTimeMillis'] ?? null;
            $trialStart = null;
            $trialEnd = null;

            if (isset($subscriptionInfo['paymentState']) && $subscriptionInfo['paymentState'] === 2) {
                $trialStart = Carbon::createFromTimestampMs($subscriptionInfo['startTimeMillis']);
                $trialEnd = $expiryTime ? Carbon::createFromTimestampMs($expiryTime) : null;
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
        if (!$subscriptionId || !$status) {
            return response()->json(['error' => 'Missing required information'], 400);
        }

        DB::beginTransaction();

        try {
            // Find or create subscription record
            $subscription = Subscription::firstOrNew([
                'provider' => $provider,
                'provider_purchase_token' => $purchaseToken,
            ]);

            $subscription->provider_subscription_id = $subscriptionId;
            // Update subscription details
            $subscription->status = $status;

            // Convert timestamp to Carbon instance if it's a timestamp
            if (is_numeric($expirationDate)) {
                $subscription->expiration_date = Carbon::createFromTimestampMs($expirationDate);
            } else if ($expirationDate) {
                $subscription->expiration_date = Carbon::parse($expirationDate);
            }

            // Handle provider-specific details
            if ($provider === 'google_pay' && $purchaseToken) {
                $subscription->provider_purchase_token = $purchaseToken;
            }

            // Update trial information
            if ($trialStart && $trialEnd) {
                $subscription->trial_start = $trialStart;
                $subscription->trial_end = $trialEnd;
            }

            // Only update user-related information if userUuid is provided
            if ($userUuid) {
                $user = User::where('uuid', $userUuid)->first();
                if ($user) {
                    $subscription->user_id = $user->id;

                    // Update user's status for backward compatibility
                    // $user->subscription_status = $status;
                    // $user->save();
                }
            }

            $subscription->save();

            DB::commit();

            Log::info("Subscription updated", [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id ?? null,
                'status' => $status,
                'provider' => $provider
            ]);

            $plan = "";

            if($subscription->provider_subscription_id === "notes_monthly_1") {
                $plan = "month";
            }
            if($subscription->provider_subscription_id === "notes_yearly_1") {
                $plan = "year";
            }

            $systemActionService = new SystemActionService();
            $systemActionService->logAction(SystemActionType::SUBSCRIPTION, [
                'user_id' => $user->id, 
                'plan' => $plan
            ]);

            return response()->json(['message' => 'Subscription updated successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to update subscription", [
                'error' => $e->getMessage(),
                'user_uuid' => $userUuid,
                'subscription_id' => $subscriptionId
            ]);

            return response()->json(['error' => 'Failed to update subscription'], 500);
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

    public function checkToken(Request $request)
    {
        $request->validate([
            'provider' => 'required|string|in:apple_pay,google_pay',
            'purchase_token' => 'required_if:provider,google_pay|string|nullable',
            'original_transaction_id' => 'required_if:provider,apple_pay|string|nullable'
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $subscription = null;

            if ($request->provider === 'google_pay') {
                $subscription = Subscription::where('provider', 'google_pay')
                    ->where('provider_purchase_token', $request->purchase_token)
                    ->first();
            } else {
                $subscription = Subscription::where('provider', 'apple_pay')
                    ->where('provider_subscription_id', $request->original_transaction_id)
                    ->first();
            }

            if (!$subscription) {
                return response()->json([
                    'error' => 'No subscription found for the provided token'
                ], 404);
            }

            // If subscription is already linked to a different user
            if ($subscription->user_id && $subscription->user_id !== $user->id) {
                return response()->json([
                    'error' => 'Subscription is already linked to another user'
                ], 400);
            }

            DB::beginTransaction();
            try {
                // Update subscription with user ID
                $subscription->user_id = $user->id;
                $subscription->save();

                // Update user's subscription status for backward compatibility
                $user->subscription_status = $subscription->status;
                $user->save();

                DB::commit();

                return response()->json([
                    'message' => 'Subscription successfully linked to user',
                    'subscription' => [
                        'status' => $subscription->status,
                        'expiration_date' => $subscription->expiration_date,
                        'trial_end' => $subscription->trial_end
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error checking subscription token', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'provider' => $request->provider
            ]);

            return response()->json([
                'error' => 'Failed to process subscription check'
            ], 500);
        }
    }
}
