<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecuritySettingsTable extends Migration
{
    public function up()
    {
        Schema::create('security_settings', function (Blueprint $table) {
            $table->id();

            // Authentication settings
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_type')->default('email');

            // Password policy
            $table->boolean('password_length_enabled')->default(true);
            $table->integer('password_min_length')->default(8);
            $table->integer('password_max_length')->default(24);
            $table->boolean('special_characters_enabled')->default(true);
            $table->integer('special_characters_min')->default(2);
            $table->integer('special_characters_max')->default(4);
            $table->boolean('periodic_password_changes_enabled')->default(true);
            $table->string('password_change_period')->default('3 months');

            // Session timeout
            $table->boolean('session_timeout_enabled')->default(true);
            $table->string('session_timeout_duration')->default('1 hour');

            // Data privacy
            $table->boolean('data_retention_enabled')->default(true);
            $table->string('data_retention_period')->default('5 years');
            $table->boolean('delete_inactive_accounts_enabled')->default(true);
            $table->string('inactive_account_period')->default('1 year');
            $table->boolean('allow_data_download')->default(true);
            $table->boolean('allow_data_deletion')->default(true);
            $table->boolean('view_privacy_policy')->default(true);
            $table->boolean('audit_logging')->default(true);

            // Monitoring
            $table->boolean('admin_activity_logs_enabled')->default(true);
            $table->boolean('failed_login_alerts_enabled')->default(true);

            $table->timestamps();
        });

        // Insert default settings
        DB::table('security_settings')->insert([
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('security_settings');
    }
}