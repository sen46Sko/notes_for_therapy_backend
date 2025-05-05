<?php

namespace App\Console\Commands;

use App\Http\Controllers\AdminNotificationController;
use App\Models\Admin;
use App\Models\SystemVersion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DetectCodeChanges extends Command
{
    protected $signature = 'platform:detect-code-changes';
    protected $description = 'Detect significant changes in the platform code';

    protected $keySources = [
        'composer.json',
        'app/Http/Controllers',
        'app/Models',
        'database/migrations',
        'config',
        'resources/js',
        'resources/css',
    ];

    protected $ignorePatterns = [
        '.env',
        '*.log',
        'storage/*',
        'node_modules/*',
        'vendor/*',
        '*.bak',
        '*.tmp',
    ];

    public function handle()
    {
        $this->info('Checking for platform code changes...');

        try {
            $currentCommit = $this->getCurrentCommitHash();

            $lastVersionRecord = SystemVersion::orderBy('created_at', 'desc')->first();
            $lastCommit = $lastVersionRecord ? $lastVersionRecord->git_commit : null;

            if (!$lastCommit || $lastCommit !== $currentCommit) {
                $changesSummary = $this->getChanges($lastCommit, $currentCommit);

                if ($this->isSignificantUpdate($changesSummary)) {
                    $newVersion = $this->generateNewVersion($lastVersionRecord);

                    $newVersionRecord = SystemVersion::create([
                        'version' => $newVersion,
                        'release_date' => now(),
                        'git_commit' => $currentCommit,
                        'description' => $this->generateDescription($changesSummary),
                        'changelog' => $changesSummary,
                    ]);

                    $this->info("Detected platform update: v{$newVersion}");

                    $this->notifyAdmins($newVersionRecord);
                } else {
                    $this->info("Changes detected, but not significant enough for version update");
                }
            } else {
                $this->info("No new code changes detected");
            }
        } catch (\Exception $e) {
            Log::error('Failed to detect code changes: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->error('Error detecting changes: ' . $e->getMessage());
        }
    }

    private function getCurrentCommitHash()
    {
        $output = [];
        exec('git rev-parse HEAD', $output);
        return trim($output[0] ?? '');
    }

    private function getChanges($lastCommit, $currentCommit)
    {
        $changelog = '';

        if (!$lastCommit) {
            $command = "git log --pretty=format:\"%h - %s (%an)\" -n 10";
            $output = [];
            exec($command, $output);
            $changelog = "Initial platform version tracking.\n\n" . implode("\n", $output);
        } else {
            $command = "git diff --name-only {$lastCommit} {$currentCommit}";
            $changedFiles = [];
            exec($command, $changedFiles);

            $significantChanges = $this->filterSignificantFiles($changedFiles);

            $command = "git log {$lastCommit}..{$currentCommit} --pretty=format:\"%h - %s (%an)\"";
            $commits = [];
            exec($command, $commits);

            $changelog = "Changed files:\n" . implode("\n", $significantChanges) . "\n\nCommits:\n" . implode("\n", $commits);
        }

        return $changelog;
    }

    private function filterSignificantFiles($files)
    {
        $significant = [];

        foreach ($files as $file) {
            $skip = false;
            foreach ($this->ignorePatterns as $pattern) {
                if (fnmatch($pattern, $file)) {
                    $skip = true;
                    break;
                }
            }

            if ($skip) continue;

            foreach ($this->keySources as $keySource) {
                if (strpos($file, $keySource) === 0) {
                    $significant[] = $file;
                    break;
                }
            }
        }

        return $significant;
    }

    private function isSignificantUpdate($changesSummary)
    {
        $changedFilesCount = substr_count($changesSummary, "\n",
            strpos($changesSummary, "Changed files:"),
            strpos($changesSummary, "Commits:") - strpos($changesSummary, "Changed files:"));

        return $changedFilesCount > 3;
    }

    private function generateNewVersion($lastVersion)
    {
        if (!$lastVersion) {
            return '1.0.0';
        }

        $parts = explode('.', $lastVersion->version);
        $parts[2] = (int)$parts[2] + 1;

        return implode('.', $parts);
    }

    private function generateDescription($changesSummary)
    {
        preg_match_all('/Changed files:\n(.*?)(?=\n\nCommits:)/s', $changesSummary, $matches);
        if (isset($matches[1][0])) {
            $files = explode("\n", $matches[1][0]);
            $files = array_slice($files, 0, min(3, count($files)));

            $areas = [];
            foreach ($files as $file) {
                if (strpos($file, 'Controllers') !== false) {
                    $areas[] = 'controllers';
                } elseif (strpos($file, 'Models') !== false) {
                    $areas[] = 'models';
                } elseif (strpos($file, 'migrations') !== false) {
                    $areas[] = 'database';
                } elseif (strpos($file, 'js') !== false || strpos($file, 'css') !== false) {
                    $areas[] = 'frontend';
                } elseif (strpos($file, 'config') !== false) {
                    $areas[] = 'configuration';
                }
            }

            $areas = array_unique($areas);

            if (count($areas) > 0) {
                return "Updates to " . implode(', ', $areas);
            }
        }

        return "Platform code update";
    }

    private function notifyAdmins($versionRecord)
    {
        $admins = Admin::all();

        $title = "Platform Updated to v{$versionRecord->version}";
        $content = "The platform has been updated to version {$versionRecord->version}.\n\n";

        if ($versionRecord->description) {
            $content .= "Summary: {$versionRecord->description}\n\n";
        }

        if ($versionRecord->changelog) {
            $content .= "Changelog:\n{$versionRecord->changelog}";
        }

        foreach ($admins as $admin) {
            AdminNotificationController::createNotification(
                $admin->id,
                'system_notification',
                'platform_update',
                $title,
                $content
            );
        }

        $this->info("Sent notifications to " . count($admins) . " admins");
    }
}