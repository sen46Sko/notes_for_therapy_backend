<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminRights extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'view_users',
        'edit_user',
        'delete_user',
        'suspend_user',
        'invite_user',
        'view_tickets',
        'respond_tickets',
        'assign_tickets',
        'close_tickets',
        'view_analytics',
        'export_analytics',
        'delete_user_data',
        'manage_login',
        'view_security_logs',
        'reset_user_password',
        'assign_roles',
        'modify_permissions',
        'view_admin_logs',
        'restrict_features',
        'view_chats',
        'respond_to_chats',
        'manage_chat_settings',
        'view_notification_setting',
        'send_notification_boolean',
    ];

    protected $casts = [
        'view_users' => 'boolean',
        'edit_user' => 'boolean',
        'delete_user' => 'boolean',
        'suspend_user' => 'boolean',
        'invite_user' => 'boolean',
        'view_tickets' => 'boolean',
        'respond_tickets' => 'boolean',
        'assign_tickets' => 'boolean',
        'close_tickets' => 'boolean',
        'view_analytics' => 'boolean',
        'export_analytics' => 'boolean',
        'delete_user_data' => 'boolean',
        'manage_login' => 'boolean',
        'view_security_logs' => 'boolean',
        'reset_user_password' => 'boolean',
        'assign_roles' => 'boolean',
        'modify_permissions' => 'boolean',
        'view_admin_logs' => 'boolean',
        'restrict_features' => 'boolean',
        'view_chats' => 'boolean',
        'respond_to_chats' => 'boolean',
        'manage_chat_settings' => 'boolean',
        'view_notification_setting' => 'boolean',
        'send_notification_boolean' => 'boolean',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
