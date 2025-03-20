<?php

namespace App\Http\Controllers;

use App\Enums\SystemActionType;
use App\Models\MonthStats;
use App\Models\YearStats;
use App\Services\SystemActionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stripe;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;

class StripeControllerV2 extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */

  private \Stripe\StripeClient $stripe;

  protected SystemActionService $systemActionService;

  public function __construct(SystemActionService $systemActionService)
  {
    Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
    $this->stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

    $this->systemActionService = $systemActionService;
  }

  private function get_plans()
  {
    $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
    $products = $stripe->products->all();
    return $products;

  }

  private function get_or_create_customer()
  {
    $user = Auth::user();
    $subscription = Subscription::where('user_id', $user->id)->first();
    if ($subscription) {
      $customer = $this->stripe->customers->retrieve($subscription->customer);
      return $customer;
    } else {

      $customer = $this->stripe->customers->create([
        'email' => $user->email,
        'name' => $user->name,
      ]);


      $subscription = new Subscription();
      $subscription->customer = $customer->id;
      $subscription->user_id = $user->id;
      $subscription->save();
    }
    return $customer;
  }

  private function retrieve_stripe_subscription(string $subscription_id)
  {
    $stripe = $this->stripe;
    try {

      $subscription = $stripe->subscriptions->retrieve(
        $subscription_id,
        [
          'expand' => ['latest_invoice.payment_intent'],
        ]
      );
      return $subscription;
    } catch (\Stripe\Exception\ApiErrorException $e) {
      return null;
    }
  }

  public function get_subscription(request $request)
  {
    $user = Auth::user();
    $subscription = Subscription::where('user_id', $user->id)->first();
    if ($subscription) {
      $stripeSubscription = $this->retrieve_stripe_subscription($subscription->subscription_id);
      if ($stripeSubscription) {
        return response()->json(['subscription' => $stripeSubscription], 200);
      }
    }
    return response()->json(['subscription' => null], 200);
  }

  public function cancel_subscription(request $request)
  {
    $user = Auth::user();
    $subscription = Subscription::where('user_id', $user->id)->first();
    if ($subscription) {
      $stripe = $this->stripe;
      $subscription_id = $subscription->subscription_id;
      try {

        $stripe->subscriptions->cancel(
          $subscription_id,
          []
        );
      } catch (\Stripe\Exception\ApiErrorException $e) {
      }
      $subscription->subscription_end = now();
      $subscription->subscription_id = null;
      $subscription->plan_id = null;
      $subscription->plan_amount = null;
      $subscription->currency = null;
      $subscription->interval = null;
      $subscription->payment_status = null;
      $subscription->save();
    }

    $this->systemActionService->logAction(SystemActionType::SUBSCRIPTION_CANCELLED, [
      'user_id' => $user->id, 
    ]);

    return response()->json(['message' => 'Subscription cancelled'], 200);
  }


  public function get_prices(
    request $request
  ) {
    $stripe = $this->stripe;

    $prices = $stripe->prices->all([
      'active' => true,
      'expand' => ['data.product'],
    ]);

    return response()->json($prices, 200);
  }

  public function initialize_subscription(
    request $request
  ) {
    $stripe = $this->stripe;
    $user = Auth::user();
    $subscription = Subscription::where('user_id', $user->id)->first();

    $stripeSubscription = null;
    if ($subscription && $subscription->subscription_id) {
      $stripeSubscription = $this->retrieve_stripe_subscription($subscription->subscription_id);
      if ($stripeSubscription->status == 'active') {
        return response()->json(['message' => 'You already have an active subscription'], 400);
      }
    }

    $validator = Validator::make($request->all(), [
      'priceId' => 'required',
    ]);

    //Send failed response if request is not valid
    if ($validator->fails()) {
      return response()->json(['error' => $validator->messages()], 200);
    }

    $customer = $this->get_or_create_customer();
    $price_id = $request->priceId;

    if (!$stripeSubscription) {
      $stripeSubscription = $stripe->subscriptions->create([
        'customer' => $customer->id,
        'items' => [
          [
            'price' => $price_id,
          ]
        ],
        'payment_behavior' => 'default_incomplete',
        'expand' => ['latest_invoice.payment_intent'],
      ]);
    }

    $plan = $stripe->prices->retrieve(
      $price_id,
      [
        'expand' => ['product'],
      ]
    );

    $subscription->subscription_id = $stripeSubscription->id;
    // Timestamp to date
    $subscription->subscription_start = date('Y-m-d H:i:s', $stripeSubscription->current_period_start);
    $subscription->plan_id = $price_id;
    $subscription->plan_amount = $plan->unit_amount;
    $subscription->currency = $plan->currency;
    $subscription->interval = $plan->recurring->interval;
    $subscription->payment_status = $stripeSubscription->latest_invoice->payment_intent->status;
    $subscription->save();

    // $ephemeralKey = $stripe->ephemeralKeys->create([
    //   'customer' => $customer->id,
    // ], [
    //   'stripe_version' => '2022-08-01',
    // ]);

    $this->systemActionService->logAction(SystemActionType::SUBSCRIPTION, [
      'user_id' => $user->id, 
      'plan' => $plan->reccuring->interval
    ]);

    return response()->json([
      'customer' => $customer->id,
      'subscriptionId' => $stripeSubscription->id,
      'clientSecret' => $stripeSubscription->latest_invoice->payment_intent->client_secret,
      // 'ephemeralKey' => $ephemeralKey->secret,
    ], 200);

  }


  public function webhook(request $request)
  {
    $payload = $request->getContent();
    $event = \Stripe\Event::constructFrom(
      json_decode($payload, true)
    );
    $stripe = $this->stripe;

    if (!$request->headers->has('stripe-signature')) {
      return response()->setStatusCode(403)->json(['error' => 'Signature not found.']);
    }

    $signature = $request->headers->get('stripe-signature');

    // Parse the message body (and check the signature if possible)
    $webhookSecret = env('STRIPE_WEBHOOK_SECRET');
    if ($webhookSecret) {
      try {
        $event = \Stripe\Webhook::constructEvent(
          $payload,
          $signature,
          $webhookSecret
        );
      } catch (Exception $e) {
        return response()->setStatusCode(403)->json(['error' => $e->getMessage()]);
      }
    } else {
      $event = $request->getParsedBody();
    }
    $type = $event['type'];
    $object = $event['data']['object'];

    // Handle the event
    // Review important events for Billing webhooks
    // https://stripe.com/docs/billing/webhooks
    switch ($type) {
      case 'invoice.paid':
        if ($object['billing_reason'] == 'subscription_create') {
          // The subscription automatically activates after successful payment
          // Set the payment method used to pay the first invoice
          // as the default payment method for that subscription
          $subscription_id = $object['subscription'];
          $payment_intent_id = $object['payment_intent'];

          # Retrieve the payment intent used to pay the subscription
          $payment_intent = $stripe->paymentIntents->retrieve(
            $payment_intent_id,
            []
          );

          try {
            $stripe->subscriptions->update(
              $subscription_id,
              ['default_payment_method' => $payment_intent->payment_method],
            );
            $stripeSubscription = $stripe->subscriptions->retrieve(
              $subscription_id,
              [
                'expand' => ['latest_invoice.payment_intent'],
              ]
            );
            $subscription = Subscription::where('subscription_id', $subscription_id)->first();
            if ($subscription) {
              $subscription->subscription_start = date('Y-m-d H:i:s', $stripeSubscription->current_period_start);
              $subscription->subscription_end = date('Y-m-d H:i:s', $stripeSubscription->current_period_end);
              $subscription->payment_status = $stripeSubscription->latest_invoice->payment_intent->status;
              $subscription->save();
            }

          } catch (Exception $e) {
          }
        }
        ;

        // database to reference when a user accesses your service to avoid hitting rate
        // limits.
        break;
      case 'invoice.payment_failed':
        // If the payment fails or the customer does not have a valid payment method,
        // an invoice.payment_failed event is sent, the subscription becomes past_due.
        // Use this webhook to notify your user that their payment has
        // failed and to retrieve new card details.
        break;
      case 'invoice.finalized':
        // If you want to manually send out invoices to your customers
        // or store them locally to reference to avoid hitting Stripe rate limits.
        break;
      case 'customer.subscription.deleted':
        // handle subscription cancelled automatically based
        // upon your subscription settings. Or if the user
        // cancels it.
        break;
      // ... handle other event types
      default:
      // Unhandled event type
    }

    return response()->json(['status' => 'success'])->setStatusCode(200);

  }
}
