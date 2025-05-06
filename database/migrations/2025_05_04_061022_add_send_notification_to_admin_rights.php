<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSendNotificationToAdminRights extends Migration
{
    public function up()
    {
        Schema::table('admin_rights', function (Blueprint $table) {
            $table->boolean('send_notification')->default(true);
        });
    }

    public function down()
    {
        Schema::table('admin_rights', function (Blueprint $table) {
            $table->dropColumn('send_notification');
        });
    }
}