<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveSendNotificationBooleanFromAdminRights extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_rights', function (Blueprint $table) {
            $table->dropColumn('send_notification_boolean');
        });
    }

    public function down()
    {
        Schema::table('admin_rights', function (Blueprint $table) {
            $table->boolean('send_notification_boolean')->default(false);
        });
    }
}
