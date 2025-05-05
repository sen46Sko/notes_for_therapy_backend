<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecuritySettings extends Model
{
    use HasFactory;

    protected $fillable = [
        // Authentication
        'two_factor_enabled',
        'two_factor_type',

        // Password policy
        'password_length_enabled',
        'password_min_length',
        'password_max_length',
        'special_characters_enabled',
        'special_characters_min',
        'special_characters_max',
        'periodic_password_changes_enabled',
        'password_change_period',

        // Session timeout
        'session_timeout_enabled',
        'session_timeout_duration',

        // Data privacy
        'data_retention_enabled',
        'data_retention_period',
        'delete_inactive_accounts_enabled',
        'inactive_account_period',
        'allow_data_download',
        'allow_data_deletion',
        'view_privacy_policy',
        'audit_logging',

        // Monitoring
        'admin_activity_logs_enabled',
        'failed_login_alerts_enabled',
    ];

    protected $casts = [
        'two_factor_enabled' => 'boolean',
        'password_length_enabled' => 'boolean',
        'special_characters_enabled' => 'boolean',
        'periodic_password_changes_enabled' => 'boolean',
        'session_timeout_enabled' => 'boolean',
        'data_retention_enabled' => 'boolean',
        'delete_inactive_accounts_enabled' => 'boolean',
        'allow_data_download' => 'boolean',
        'allow_data_deletion' => 'boolean',
        'view_privacy_policy' => 'boolean',
        'audit_logging' => 'boolean',
        'admin_activity_logs_enabled' => 'boolean',
        'failed_login_alerts_enabled' => 'boolean',
    ];

    /**
     * Get the default security settings
     */
    public static function getDefault()
    {
        return [
            'two_factor_enabled' => false,
            'two_factor_type' => 'email',
            'password_length_enabled' => true,
            'password_min_length' => 8,
            'password_max_length' => 24,
            'special_characters_enabled' => true,
            'special_characters_min' => 2,
            'special_characters_max' => 4,
            'periodic_password_changes_enabled' => true,
            'password_change_period' => '3 months',
            'session_timeout_enabled' => true,
            'session_timeout_duration' => '1 hour',
            'data_retention_enabled' => true,
            'data_retention_period' => '5 years',
            'delete_inactive_accounts_enabled' => true,
            'inactive_account_period' => '1 year',
            'allow_data_download' => true,
            'allow_data_deletion' => true,
            'view_privacy_policy' => true,
            'audit_logging' => true,
            'admin_activity_logs_enabled' => true,
            'failed_login_alerts_enabled' => true,
        ];
    }
}