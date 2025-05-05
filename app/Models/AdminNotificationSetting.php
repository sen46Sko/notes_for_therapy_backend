<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminNotificationSetting extends Model
{
    use HasFactory;

    /**
     *
     * @var array
     */
    protected $fillable = [
        'is_global',
        'admin_id',
        'notifications_enabled',
        'notification_frequency',
        'delivery_method',
        'critical_system_errors',
        'platform_updates',
        'change_history_audit',
        'new_user_registration',
        'permission_role_changes',
        'failed_login_attempts',
        'user_comments_feedback',
        'view_user_analytics',
        'export_user_data',
        'delete_user_data',
        'api_alerts_webhooks',
        'activity_log',
        'automatic_retries',
        'connect_external_services',
    ];

    /**
     *
     * @var array
     */
    protected $casts = [
        'is_global' => 'boolean',
        'notifications_enabled' => 'boolean',
        'critical_system_errors' => 'boolean',
        'platform_updates' => 'boolean',
        'change_history_audit' => 'boolean',
        'new_user_registration' => 'boolean',
        'permission_role_changes' => 'boolean',
        'failed_login_attempts' => 'boolean',
        'user_comments_feedback' => 'boolean',
        'view_user_analytics' => 'boolean',
        'export_user_data' => 'boolean',
        'delete_user_data' => 'boolean',
        'api_alerts_webhooks' => 'boolean',
        'activity_log' => 'boolean',
        'automatic_retries' => 'boolean',
        'connect_external_services' => 'boolean',
    ];

    /**

     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}