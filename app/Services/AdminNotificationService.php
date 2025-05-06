<?php

namespace App\Services;

use App\Http\Controllers\AdminNotificationController;
use App\Models\User;

class AdminNotificationService
{
    /**
     *
     */
    public function sendSystemAlert($adminId, $subtype, $title, $content)
    {
        return AdminNotificationController::createNotification(
            $adminId,
            'system_alert',
            $subtype,
            $title,
            $content
        );
    }

    /**
     *
     */
    public function sendUserNotification($adminId, $subtype, $title, $content)
    {
        return AdminNotificationController::createNotification(
            $adminId,
            'user_notification',
            $subtype,
            $title,
            $content
        );
    }

    /**
     *
     */
    public function sendAdvancedNotification($adminId, $subtype, $title, $content)
    {
        return AdminNotificationController::createNotification(
            $adminId,
            'advanced_notification',
            $subtype,
            $title,
            $content
        );
    }

    /**
     *
     */
    public function notifyAllAdmins($type, $subtype, $title, $content)
    {

        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            AdminNotificationController::createNotification(
                $admin->id,
                $type,
                $subtype,
                $title,
                $content
            );
        }
    }

    /**
     *
     */
    public function sendCriticalErrorAlert($error)
    {
        $title = 'Критическая ошибка системы';
        $content = "Обнаружена критическая ошибка: {$error}";

        $this->notifyAllAdmins('system_alert', 'critical_error', $title, $content);
    }

    /**
     *
     */
    public function notifyAboutNewUser($newUser)
    {
        $title = 'Новая регистрация пользователя';
        $content = "Зарегистрирован новый пользователь: {$newUser->name} ({$newUser->email})";

        $this->notifyAllAdmins('user_notification', 'new_user', $title, $content);
    }

    /**
     *
     */
    public function notifyAboutFailedLogin($userEmail, $ipAddress)
    {
        $title = 'Неудачная попытка входа';
        $content = "Неудачная попытка входа для пользователя {$userEmail} с IP-адреса {$ipAddress}";

        $this->notifyAllAdmins('user_notification', 'failed_login', $title, $content);
    }
}