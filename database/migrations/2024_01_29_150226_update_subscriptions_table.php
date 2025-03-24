<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('subscriptions', function (Blueprint $table) {
            // Add new columns
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->dateTime('subscription_start')->nullable();
            $table->dateTime('subscription_end')->nullable();
            $table->string('customer')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->decimal('plan_amount', 10, 2)->nullable();
            $table->string('currency')->nullable();
            $table->string('interval')->nullable();
            $table->string('payment_status')->nullable();
            $table->text('comments')->nullable();
            $table->string('type')->nullable();

            // You may want to add foreign key constraints for new IDs like `subscription_id`, `plan_id`, etc.
            // $table->foreign('plan_id')->references('id')->on('plans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('subscriptions', function (Blueprint $table) {
            // Drop the added columns
            $table->dropColumn([
                'subscription_id',
                'subscription_start',
                'subscription_end',
                'customer',
                'plan_id',
                'plan_amount',
                'currency',
                'interval',
                'payment_status',
                'comments',
                'type'
            ]);
        });
    }
}
