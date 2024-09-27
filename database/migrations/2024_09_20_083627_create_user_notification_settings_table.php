<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserNotificationSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_notification_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')
               ->on('users')
               ->references('id')
               ->onDelete('cascade');
            $table->boolean('show_notifications')->default(true);
            $table->boolean('sound')->default(true);
            $table->boolean('preview')->default(true);
            $table->boolean('mail')->default(true);
            $table->boolean('marketing_ads')->default(true);
            $table->boolean('reminders')->default(true);
            $table->boolean('mood')->default(true);
            $table->boolean('notes')->default(true);
            $table->boolean('symptoms')->default(true);
            $table->boolean('goals')->default(true);
            $table->boolean('homework')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_notification_settings');
    }
}
