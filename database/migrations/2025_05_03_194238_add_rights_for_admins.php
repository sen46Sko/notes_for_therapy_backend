<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\AdminRights;

class AddRightsForAdmins extends Migration
{
    public function up()
    {
        $fullRights = [
            'view_users' => true,
            'edit_user' => true,
            'delete_user' => true,
            'suspend_user' => true,
            'invite_user' => true,
            'view_tickets' => true,
            'respond_tickets' => true,
            'assign_tickets' => true,
            'close_tickets' => true,
            'view_analytics' => true,
            'export_analytics' => true,
            'delete_user_data' => true,
            'manage_login' => true,
            'view_security_logs' => true,
            'reset_user_password' => true,
            'assign_roles' => true,
            'modify_permissions' => true,
            'view_admin_logs' => true,
            'restrict_features' => true,
            'view_chats' => true,
            'respond_to_chats' => true,
            'manage_chat_settings' => true,
            'view_notification_setting' => true,
            'send_notification' => true
        ];

        AdminRights::updateOrCreate(
            ['admin_id' => 1],
            $fullRights
        );

        AdminRights::updateOrCreate(
            ['admin_id' => 2],
            $fullRights
        );
    }

    public function down()
    {

        AdminRights::whereIn('admin_id', [1, 2])->delete();
    }
}