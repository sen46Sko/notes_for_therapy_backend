<?php

namespace App\Services;

use App\Enums\SystemActionType;
use App\Models\SystemAction;
use Illuminate\Support\Facades\Log;

class SystemActionService
{
    public function logAction(SystemActionType $actionType, ?array $payload = null): SystemAction
    {
        try {
            return SystemAction::create([
                'action_type' => $actionType,
                'payload' => $payload
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log system action', [
                'action_type' => $actionType,
                'payload' => $payload,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function getActionStats(?\DateTime $startDate = null, ?\DateTime $endDate = null): array
    {
        $query = SystemAction::query();

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return [
            'total_actions' => $query->count(),
            'actions_by_type' => $query->selectRaw('action_type, COUNT(*) as count')
                ->groupBy('action_type')
                ->get()
                ->pluck('count', 'action_type')
                ->toArray(),
            'latest_actions' => $query->latest()
                ->take(10)
                ->get()
        ];
    }
}
