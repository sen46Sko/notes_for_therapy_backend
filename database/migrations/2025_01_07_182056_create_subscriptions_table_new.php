<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTableNew extends Migration
{
    public function up()
    {
        Schema::dropIfExists('subscriptions');

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('provider'); // 'apple_pay' or 'google_pay'
            $table->string('provider_subscription_id'); // originalTransactionId for Apple, subscriptionId for Google
            $table->string('provider_purchase_token')->nullable(); // Only for Google Pay
            $table->string('status')->default('pending'); // pending, active, canceled, expired, etc.
            $table->timestamp('trial_start')->nullable();
            $table->timestamp('trial_end')->nullable();
            $table->timestamp('expiration_date')->nullable();
            $table->string('coupon_code')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraint
            $table->foreign('user_id')
               ->on('users')
               ->references('id')
               ->onDelete('cascade');

            // Composite index for efficient lookups
            $table->unique(['provider', 'provider_subscription_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}
