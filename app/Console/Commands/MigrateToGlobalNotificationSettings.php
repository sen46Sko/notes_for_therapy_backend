<?php

namespace App\Console\Commands;

use App\Models\AdminNotificationSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;

class MigrateToGlobalNotificationSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:migrate-to-global';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate individual notification settings to global settings';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting migration to global notification settings...');

        // Check if there are any individual settings
        $individualSettings = AdminNotificationSetting::where('is_global', false)->get();
        $count = $individualSettings->count();

        if ($count === 0) {
            $this->info('No individual notification settings found.');

            // Check if global settings exist
            $globalSettings = AdminNotificationSetting::where('is_global', true)->first();
            if (!$globalSettings) {
                $this->info('Creating default global settings...');

                // Get first admin as a reference for global settings
                $admin = Admin::first();
                if (!$admin) {
                    $this->error('No admin found in the database. Cannot create global settings.');
                    return 1;
                }

                $this->createDefaultGlobalSettings($admin->id);
            } else {
                $this->info('Global settings already exist.');
            }

            return 0;
        }

        $this->info("Found {$count} individual notification setting(s).");

        // Ask for confirmation
        if (!$this->confirm('Are you sure you want to migrate individual settings to global? This will delete all individual settings.')) {
            $this->info('Operation cancelled.');
            return 1;
        }

        // Create global settings from the first individual settings if not exist
        $globalSettings = AdminNotificationSetting::where('is_global', true)->first();
        if (!$globalSettings) {
            $this->info('Creating global settings based on the first individual settings...');
            $firstSettings = $individualSettings->first();

            $globalSettings = new AdminNotificationSetting();
            $globalSettings->is_global = true;
            $globalSettings->admin_id = $firstSettings->admin_id;
            $globalSettings->notifications_enabled = $firstSettings->notifications_enabled;
            $globalSettings->notification_frequency = $firstSettings->notification_frequency;
            $globalSettings->delivery_method = $firstSettings->delivery_method;
            $globalSettings->critical_system_errors = $firstSettings->critical_system_errors;
            $globalSettings->platform_updates = $firstSettings->platform_updates;
            $globalSettings->change_history_audit = $firstSettings->change_history_audit;
            $globalSettings->new_user_registration = $firstSettings->new_user_registration;
            $globalSettings->permission_role_changes = $firstSettings->permission_role_changes;
            $globalSettings->failed_login_attempts = $firstSettings->failed_login_attempts;
            $globalSettings->user_comments_feedback = $firstSettings->user_comments_feedback;
            $globalSettings->view_user_analytics = $firstSettings->view_user_analytics;
            $globalSettings->export_user_data = $firstSettings->export_user_data;
            $globalSettings->delete_user_data = $firstSettings->delete_user_data;
            $globalSettings->api_alerts_webhooks = $firstSettings->api_alerts_webhooks;
            $globalSettings->activity_log = $firstSettings->activity_log;
            $globalSettings->automatic_retries = $firstSettings->automatic_retries;
            $globalSettings->connect_external_services = $firstSettings->connect_external_services;
            $globalSettings->save();

            $this->info('Global settings created successfully.');
        } else {
            $this->info('Global settings already exist.');
        }

        // Delete all individual settings
        $deleted = AdminNotificationSetting::where('is_global', false)->delete();
        $this->info("Deleted {$deleted} individual notification setting(s).");

        $this->info('Migration completed successfully.');
        return 0;
    }

    /**
     * Create default global settings
     *
     * @param int $adminId
     */
    private function createDefaultGlobalSettings($adminId)
    {
        $settings = new AdminNotificationSetting();
        $settings->is_global = true;
        $settings->admin_id = $adminId;
        $settings->notifications_enabled = true;
        $settings->notification_frequency = 'instant';
        $settings->delivery_method = 'email';
        $settings->critical_system_errors = true;
        $settings->platform_updates = true;
        $settings->change_history_audit = true;
        $settings->new_user_registration = true;
        $settings->permission_role_changes = true;
        $settings->failed_login_attempts = true;
        $settings->user_comments_feedback = true;
        $settings->view_user_analytics = true;
        $settings->export_user_data = true;
        $settings->delete_user_data = true;
        $settings->api_alerts_webhooks = true;
        $settings->activity_log = true;
        $settings->automatic_retries = true;
        $settings->connect_external_services = true;
        $settings->save();

        $this->info('Default global settings created successfully.');
    }
}