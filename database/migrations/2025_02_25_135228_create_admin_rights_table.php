<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminRightsTable extends Migration
{
    public function up()
    {
        Schema::create('admin_rights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->boolean('view_users')->default(false);
            $table->boolean('edit_user')->default(false);
            $table->boolean('delete_user')->default(false);
            $table->boolean('suspend_user')->default(false);
            $table->boolean('invite_user')->default(false);
            $table->boolean('view_tickets')->default(false);
            $table->boolean('respond_tickets')->default(false);
            $table->boolean('assign_tickets')->default(false);
            $table->boolean('close_tickets')->default(false);
            $table->boolean('view_analytics')->default(false);
            $table->boolean('export_analytics')->default(false);
            $table->boolean('delete_user_data')->default(false);
            $table->boolean('manage_login')->default(false);
            $table->boolean('view_security_logs')->default(false);
            $table->boolean('reset_user_password')->default(false);
            $table->boolean('assign_roles')->default(false);
            $table->boolean('modify_permissions')->default(false);
            $table->boolean('view_admin_logs')->default(false);
            $table->boolean('restrict_features')->default(false);
            $table->boolean('view_chats')->default(false);
            $table->boolean('respond_to_chats')->default(false);
            $table->boolean('manage_chat_settings')->default(false);
            $table->boolean('view_notification_setting')->default(false);
            $table->boolean('send_notification_boolean')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_rights');
    }
};
