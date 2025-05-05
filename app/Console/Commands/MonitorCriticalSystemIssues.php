<?php

namespace App\Console\Commands;

use App\Http\Controllers\AdminNotificationController;
use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MonitorCriticalSystemIssues extends Command
{
    protected $signature = 'system:monitor-critical';
    protected $description = 'Monitor critical system components and notify admins if problems detected';

    public function handle()
    {
        $this->info('Starting critical system monitoring...');

        try {
            $this->checkDatabaseConnection();

            $this->checkDiskSpace();

            $this->checkServerLoad();

            $this->info('All critical systems seem to be working fine.');
        } catch (\Exception $e) {
            Log::error('Critical system monitoring failed: ' . $e->getMessage());
            $this->error('Monitoring failed: ' . $e->getMessage());
        }
    }

    private function checkDatabaseConnection()
    {
        try {
            $this->info('Checking database connection...');
            DB::connection()->getPdo();
            $this->info('Database connection successful.');
        } catch (\Exception $e) {
            $this->error('Database connection failed: ' . $e->getMessage());
            $this->notifyCriticalError(
                'Database Connection Failed',
                'The application cannot connect to the database. Error: ' . $e->getMessage()
            );
            throw $e;
        }
    }

    private function checkDiskSpace()
    {
        $this->info('Checking disk space...');
        $freeSpace = disk_free_space('/');
        $totalSpace = disk_total_space('/');
        $freePercentage = round(($freeSpace / $totalSpace) * 100, 2);

        $this->info("Free disk space: {$freePercentage}%");


        if ($freePercentage < 5) {
            $this->error("Critical disk space warning: only {$freePercentage}% left");
            $this->notifyCriticalError(
                'Critical Disk Space Warning',
                "The server has critically low disk space. Only {$freePercentage}% free space remaining. Immediate action required."
            );
        }

        elseif ($freePercentage < 10) {
            $this->warn("Low disk space warning: only {$freePercentage}% left");
            $this->notifyCriticalError(
                'Low Disk Space Warning',
                "The server is running low on disk space. Only {$freePercentage}% free space remaining. Please take action soon."
            );
        }
    }

    private function checkServerLoad()
    {
        $this->info('Checking server load...');


        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $this->info("Current server load: {$load[0]}");


            $coreCount = $this->getCpuCoreCount();
            $loadPerCore = $load[0] / $coreCount;

            if ($loadPerCore > 1.5) {
                $this->error("Critical server load: {$load[0]} with {$coreCount} cores");
                $this->notifyCriticalError(
                    'Critical Server Load',
                    "The server is experiencing extremely high load ({$load[0]} on {$coreCount} cores). Performance may be severely impacted."
                );
            }
        }

        if (file_exists('/proc/meminfo')) {
            $meminfo = file_get_contents('/proc/meminfo');
            preg_match('/MemTotal:\s+(\d+)\s+kB/', $meminfo, $matches);
            $totalMemory = isset($matches[1]) ? $matches[1] : 0;

            preg_match('/MemAvailable:\s+(\d+)\s+kB/', $meminfo, $matches);
            $availableMemory = isset($matches[1]) ? $matches[1] : 0;

            if ($totalMemory > 0) {
                $memPercentage = round(($availableMemory / $totalMemory) * 100, 2);
                $this->info("Available memory: {$memPercentage}%");

                if ($memPercentage < 5) {
                    $this->error("Critical memory shortage: only {$memPercentage}% available");
                    $this->notifyCriticalError(
                        'Critical Memory Shortage',
                        "The server is critically low on memory. Only {$memPercentage}% available. System stability may be compromised."
                    );
                }
            }
        }
    }

    private function getCpuCoreCount()
    {
        $cores = 1;


        if (is_file('/proc/cpuinfo')) {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            preg_match_all('/^processor/m', $cpuinfo, $matches);
            $cores = count($matches[0]);
        }

        elseif (PHP_OS_FAMILY === 'Windows') {
            $process = popen('wmic cpu get NumberOfCores', 'rb');
            if (false !== $process) {
                fgets($process);
                $cores = intval(fgets($process));
                pclose($process);
            }
        }

        return max(1, $cores);
    }

    private function notifyCriticalError($title, $message)
    {
        $this->info("Sending critical error notification: {$title}");

        $admins = Admin::all();

        foreach ($admins as $admin) {
            AdminNotificationController::createNotification(
                $admin->id,
                'system_notification',
                'critical_error',
                $title,
                $message
            );
        }
    }
}