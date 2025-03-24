<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateHomeworksNotificationIdForeign extends Migration
{
    public function up()
    {
        Schema::table('homework', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['notification_id']);

            // Add the foreign key constraint with onDelete('SET NULL')
            $table->foreign('notification_id')
                ->references('id')->on('notifications')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('homework', function (Blueprint $table) {
            // Drop the updated foreign key constraint
            $table->dropForeign(['notification_id']);

            // Revert to the previous state without onDelete action
            $table->foreign('notification_id')
                ->references('id')->on('notifications');
        });
    }
}
