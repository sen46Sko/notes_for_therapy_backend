<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('admin_id');
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');

            $table->boolean('notifications_enabled')->default(true);
            $table->enum('notification_frequency', ['instant', 'daily', 'weekly', 'monthly', 'custom'])->default('instant');
            $table->enum('delivery_method', ['email', 'all'])->default('email');

            $table->boolean('critical_system_errors')->default(true);
            $table->boolean('platform_updates')->default(true);
            $table->boolean('change_history_audit')->default(true);

            $table->boolean('new_user_registration')->default(true);
            $table->boolean('permission_role_changes')->default(true);
            $table->boolean('failed_login_attempts')->default(true);
            $table->boolean('user_comments_feedback')->default(true);
            $table->boolean('view_user_analytics')->default(true);
            $table->boolean('export_user_data')->default(true);
            $table->boolean('delete_user_data')->default(true);

            $table->boolean('api_alerts_webhooks')->default(true);
            $table->boolean('activity_log')->default(true);
            $table->boolean('automatic_retries')->default(true);
            $table->boolean('connect_external_services')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_notification_settings');
    }
};