<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AllowNullAdminIdInAdminNotificationSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_notification_settings', function (Blueprint $table) {

            $table->dropForeign(['admin_id']);

            $table->integer('admin_id')->unsigned()->nullable()->change();

            $table->foreign('admin_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admin_notification_settings', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);

            $table->integer('admin_id')->unsigned()->nullable(false)->change();

            $table->foreign('admin_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
}