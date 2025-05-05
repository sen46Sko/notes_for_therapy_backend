<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\SecuritySettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\AdminDevice;
use App\Models\AdminActivityLog;
use App\Models\FailedLoginAttempt;

class SecuritySettingsController extends Controller
{
    public function getSettings()
    {
        try {
            $settings = SecuritySettings::first();

            if (!$settings) {

                $settings = SecuritySettings::create(SecuritySettings::getDefault());
            }


            $formattedSettings = $this->formatSettingsForFrontend($settings);

            return response()->json($formattedSettings);
        } catch (\Exception $e) {
            Log::error('Failed to get security settings: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to get security settings'], 500);
        }
    }

    public function updateSettings(Request $request)
    {
        try {
            $settings = SecuritySettings::first();

            if (!$settings) {
                $settings = new SecuritySettings();
            }


            $data = $this->formatSettingsFromFrontend($request->all());
            $settings->fill($data);
            $settings->save();


            if (isset($data['two_factor_enabled'])) {
                Admin::query()->update([
                    'two_factor_enabled' => $data['two_factor_enabled']
                ]);
            }

            return response()->json([
                'message' => 'Security settings updated successfully',
                'settings' => $this->formatSettingsForFrontend($settings)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update security settings: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update security settings'], 500);
        }
    }

    public function resetToDefault()
    {
        try {
            $settings = SecuritySettings::first();

            if (!$settings) {
                $settings = new SecuritySettings();
            }

            $defaultSettings = SecuritySettings::getDefault();
            $settings->fill($defaultSettings);
            $settings->save();

            Admin::query()->update([
                'two_factor_enabled' => $defaultSettings['two_factor_enabled']
            ]);

            return response()->json([
                'message' => 'Security settings reset to default',
                'settings' => $this->formatSettingsForFrontend($settings)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to reset security settings: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to reset security settings'], 500);
        }
    }


    private function formatSettingsForFrontend($settings)
    {
         $devices = AdminDevice::with('admin:id,name')
                ->where('is_active', true)
                ->orderBy('last_active_at', 'desc')
                ->take(10)
                ->get()
                ->map(function ($device) {
                    return [
                        'id' => $device->id,
                        'type' => $device->device_type,
                        'name' => $device->admin->name,
                        'ipAddress' => $device->ip_address,
                        'lastActive' => $device->last_active_at->format('Y-m-d H:i:s'),
                        'isBlocked' => $device->is_blocked,
                    ];
                });

            $activityLogs = AdminActivityLog::with('admin:id,name')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get()
                ->map(function ($log) {
                    return [
                        'type' => $log->device_type,
                        'name' => $log->admin->name,
                        'ipAddress' => $log->ip_address,
                        'date' => $log->created_at->format('M d, Y'),
                        'time' => $log->created_at->format('g:i A'),
                        'action' => $log->action,
                    ];
                });

            $failedAttempts = FailedLoginAttempt::orderBy('attempted_at', 'desc')
                ->take(10)
                ->get()
                ->map(function ($attempt) {
                    return [
                        'type' => $attempt->device_type,
                        'name' => $attempt->email,
                        'ipAddress' => $attempt->ip_address,
                        'date' => $attempt->attempted_at->format('M d, Y'),
                        'time' => $attempt->attempted_at->format('g:i A'),
                    ];
                });
        return [
            'authentication' => [
                'twoFactorEnabled' => $settings->two_factor_enabled,
                'twoFactorType' => $settings->two_factor_type,
                'passwordPolicy' => [
                    'minMaxPasswordLength' => [
                        'enabled' => $settings->password_length_enabled,
                        'min' => (string) $settings->password_min_length,
                        'max' => (string) $settings->password_max_length,
                    ],
                    'specialCharacters' => [
                        'enabled' => $settings->special_characters_enabled,
                        'min' => (string) $settings->special_characters_min,
                        'max' => (string) $settings->special_characters_max,
                    ],
                    'periodicPasswordChanges' => [
                        'enabled' => $settings->periodic_password_changes_enabled,
                        'time' => $settings->password_change_period,
                    ],
                ],
            ],
            'sessionTimeout' => [
                'enabled' => $settings->session_timeout_enabled,
                'duration' => $settings->session_timeout_duration,
            ],
            'deviceManagement' => [
                'devices' => $devices,
            ],
            'dataPrivacy' => [
                'dataRetention' => [
                    'enabled' => $settings->data_retention_enabled,
                    'period' => $settings->data_retention_period,
                ],
                'deleteInactiveAccounts' => [
                    'enabled' => $settings->delete_inactive_accounts_enabled,
                    'period' => $settings->inactive_account_period,
                ],
                'userDataRequests' => [
                    'allowDownload' => $settings->allow_data_download,
                    'allowDeletion' => $settings->allow_data_deletion,
                ],
                'viewPrivacyPolicy' => $settings->view_privacy_policy,
                'auditLogging' => $settings->audit_logging,
            ],
            'monitoring' => [
                'adminActivityLogs' => [
                    'enabled' => $settings->admin_activity_logs_enabled,
                    'logs' => $activityLogs,
                ],
                'failedLoginAttempts' => [
                    'alertEnabled' => $settings->failed_login_alerts_enabled,
                    'attempts' => $failedAttempts,
                ],
            ],
        ];
    }

    private function formatSettingsFromFrontend($data)
    {
        return [
            'two_factor_enabled' => $data['authentication']['twoFactorEnabled'] ?? false,
            'two_factor_type' => $data['authentication']['twoFactorType'] ?? 'email',

            'password_length_enabled' => $data['authentication']['passwordPolicy']['minMaxPasswordLength']['enabled'] ?? true,
            'password_min_length' => (int) ($data['authentication']['passwordPolicy']['minMaxPasswordLength']['min'] ?? 8),
            'password_max_length' => (int) ($data['authentication']['passwordPolicy']['minMaxPasswordLength']['max'] ?? 24),

            'special_characters_enabled' => $data['authentication']['passwordPolicy']['specialCharacters']['enabled'] ?? true,
            'special_characters_min' => (int) ($data['authentication']['passwordPolicy']['specialCharacters']['min'] ?? 2),
            'special_characters_max' => (int) ($data['authentication']['passwordPolicy']['specialCharacters']['max'] ?? 4),

            'periodic_password_changes_enabled' => $data['authentication']['passwordPolicy']['periodicPasswordChanges']['enabled'] ?? true,
            'password_change_period' => $data['authentication']['passwordPolicy']['periodicPasswordChanges']['time'] ?? '3 months',

            'session_timeout_enabled' => $data['sessionTimeout']['enabled'] ?? true,
            'session_timeout_duration' => $data['sessionTimeout']['duration'] ?? '1 hour',

            'data_retention_enabled' => $data['dataPrivacy']['dataRetention']['enabled'] ?? true,
            'data_retention_period' => $data['dataPrivacy']['dataRetention']['period'] ?? '5 years',

            'delete_inactive_accounts_enabled' => $data['dataPrivacy']['deleteInactiveAccounts']['enabled'] ?? true,
            'inactive_account_period' => $data['dataPrivacy']['deleteInactiveAccounts']['period'] ?? '1 year',

            'allow_data_download' => $data['dataPrivacy']['userDataRequests']['allowDownload'] ?? true,
            'allow_data_deletion' => $data['dataPrivacy']['userDataRequests']['allowDeletion'] ?? true,

            'view_privacy_policy' => $data['dataPrivacy']['viewPrivacyPolicy'] ?? true,
            'audit_logging' => $data['dataPrivacy']['auditLogging'] ?? true,

            'admin_activity_logs_enabled' => $data['monitoring']['adminActivityLogs']['enabled'] ?? true,
            'failed_login_alerts_enabled' => $data['monitoring']['failedLoginAttempts']['alertEnabled'] ?? true,
        ];
    }
}