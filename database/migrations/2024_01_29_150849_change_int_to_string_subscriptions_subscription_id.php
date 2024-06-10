<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeIntToStringSubscriptionsSubscriptionId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Change unsignedInteger to string on column `subscription_id`
            $table->string('subscription_id')->nullable()->change();
            $table->string('plan_Id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Revert
            $table->unsignedBigInteger('subscription_id')->nullable()->change();
            $table->unsignedBigInteger('plan_Id')->nullable()->change();
        });
    }
}
