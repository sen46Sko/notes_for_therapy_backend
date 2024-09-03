<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemindAtToHomework extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('homework', function (Blueprint $table) {
            // Add remind_at of type datetime
            $table->dateTime('remind_at')->nullable();

            $table->unsignedInteger('notification_id')->nullable();
            $table->foreign('notification_id')
                ->on('notifications')
                ->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('homework', function (Blueprint $table) {
            // Drop the remind_at column
            $table->dropColumn('remind_at');

            $table->dropForeign(['notification_id']);
            $table->dropColumn('notification_id');
        });
    }
}
