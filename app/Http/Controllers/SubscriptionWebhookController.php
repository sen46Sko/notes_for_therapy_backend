<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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

    public function handleGooglePayWebhook(Request $request)
    {
        $encodedData = $request->input('message.data');
        if (!$encodedData) {
            return response()->json(['error' => 'Missing encoded data'], 400);
        }

        try {
            $decodedData = $this->decodeGoogleNotification($encodedData);
            Log::info('Google Pay Webhook received', $decodedData);

            return $this->processGoogleSubscriptionUpdate($decodedData);
        } catch (\Exception $e) {
            Log::error('Error processing Google Pay webhook: ' . $e->getMessage());
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

    private function decodeGoogleNotification($encodedData)
    {
        $decodedJson = base64_decode($encodedData);
        $decodedData = json_decode($decodedJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON in decoded data');
        }

        return $decodedData;
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

    return $this->updateSubscription($userUuid, $subscriptionId, $newStatus, $expirationDate, 'apple_pay');
}

    private function processGoogleSubscriptionUpdate($decodedData)
    {
        $notificationType = $decodedData['subscriptionNotification']['notificationType'] ?? null;
        $purchaseToken = $decodedData['subscriptionNotification']['purchaseToken'] ?? null;
        $subscriptionId = $decodedData['subscriptionNotification']['subscriptionId'] ?? null;

        if (!$notificationType || !$purchaseToken || !$subscriptionId) {
            return response()->json(['error' => 'Missing required information'], 400);
        }

        $userUuid = $this->getUserIdFromPurchaseToken($purchaseToken);
        $status = $this->mapGoogleStatusToInternal($notificationType);
        $expirationDate = $decodedData['eventTimeMillis'] ?? null;

        return $this->updateSubscription($userUuid, $subscriptionId, $status, $expirationDate, 'google_pay', $purchaseToken);
    }

    private function updateSubscription($userUuid, $subscriptionId, $status, $expirationDate, $provider, $purchaseToken = null)
    {
        if (!$userUuid || !$subscriptionId || !$status) {
            return response()->json(['error' => 'Missing required information'], 400);
        }

        $user = User::where('uuid', $userUuid)->first();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // $subscription = Subscription::updateOrCreate(
        //     ['user_id' => $user->id, 'provider' => $provider, 'provider_subscription_id' => $subscriptionId],
        //     [
        //         'status' => $status,
        //         'expiration_date' => $expirationDate ? Carbon::createFromTimestamp($expirationDate / 1000) : null,
        //         'purchase_token' => $purchaseToken,
        //     ]
        // );

        $user->subscription_status = $status;
        $user->save();

        Log::info("Subscription updated for user {$user->id}");

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

    private function mapGoogleStatusToInternal($notificationType)
    {
        switch ($notificationType) {
            case 1: // SUBSCRIPTION_RECOVERED
            case 2: // SUBSCRIPTION_RENEWED
            case 4: // SUBSCRIPTION_PURCHASED
            case 7: // SUBSCRIPTION_RESTARTED
                return 'active';
            case 3: // SUBSCRIPTION_CANCELED
                return 'cancelled';
            case 5: // SUBSCRIPTION_ON_HOLD
                return 'on_hold';
            case 6: // SUBSCRIPTION_IN_GRACE_PERIOD
                return 'active';
            case 10: // SUBSCRIPTION_PAUSED
                return 'paused';
            case 12: // SUBSCRIPTION_REVOKED
            case 13: // SUBSCRIPTION_EXPIRED
                return 'expired';
            default:
                return 'inactive';
        }
    }

    private function getCurrentSubscriptionStatus($userUuid, $subscriptionId)
    {
        // Implement this method to fetch the current subscription status from your database
        // Return the current status or a default value if not found
        return 'inactive'; // Default implementation - replace with actual database query
    }

    private function getUserIdFromPurchaseToken($purchaseToken)
    {
        // TODO: Implement this method to retrieve the user ID associated with the purchase token
        // This might involve querying your database or making an API call to Google Play Developer API
        return null;
    }
}
