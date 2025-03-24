<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUniqueConstraintForSubscriptionsTable extends Migration
{
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Drop the existing unique constraint
            $table->dropUnique(['provider', 'provider_subscription_id']);

            // Add new unique constraint
            $table->unique(['provider', 'provider_purchase_token']);
        });
    }

    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Remove the new constraint
            $table->dropUnique(['provider', 'provider_purchase_token']);

            // Add back the original constraint
            $table->unique(['provider', 'provider_subscription_id']);
        });
    }
}
