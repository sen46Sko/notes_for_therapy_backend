<?php

namespace App\Helper;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class NotificationRepeatHelper
{
    private $repeatData;

    public function __construct(string $repeatJson)
    {
        if (empty($repeatJson)) {
            throw new Exception('Repeat data is empty');
        }
        $this->repeatData = json_decode($repeatJson, true);

        if (!$this->isValidRepeatData()) {
            throw new Exception('Invalid repeat data format');
        }
    }

    private function isValidRepeatData(): bool
    {
        $validTypes = ['weekdays', 'weekend', 'daily', 'biweekly', 'weekly', 'monthly', 'custom', 'custom-biweekly'];
        return isset($this->repeatData['type']) &&
               in_array($this->repeatData['type'], $validTypes) &&
               (!isset($this->repeatData['custom']) || is_array($this->repeatData['custom']));
    }

    public function getNextNotificationDate(string $previousDateString): Carbon
    {
        $previousDate = Carbon::parse($previousDateString);
        $previousDate->setDate(Carbon::now()->year, Carbon::now()->month, Carbon::now()->day);
        $nextDate = $previousDate->copy();

        switch ($this->repeatData['type']) {
            case 'weekdays':
                Log::info('weekdays');
                if (is_array($this->repeatData['custom'])) {
                    Log::info('applyinh custom next date');
                    $nextDate = $this->getNextCustomDate($nextDate);
                    break;
                }
                $nextDate->addWeekday();
                break;
            case 'weekend':
                Log::info('weekend');
                $nextDate->nextWeekendDay();
                break;
            case 'daily':
                Log::info('daily');
                $nextDate->addDay();
                break;
            case 'biweekly':
                Log::info('biweekly');
                if (is_array($this->repeatData['custom'])) {
                    Log::info('applyinh custom next date');
                    $nextDate = $this->getNextCustomDate($nextDate);
                    break;
                }
                $nextDate->addWeeks(2);
                break;
            case 'weekly':
                Log::info('weekly');
                if (is_array($this->repeatData['custom'])) {
                    Log::info('applyinh custom next date');
                    $nextDate = $this->getNextCustomDate($nextDate);
                    break;
                }
                $nextDate->addWeek();
                break;
            case 'monthly':
                $nextDate->addMonth();
                break;
            // case 'custom':
            //     $nextDate = $this->getNextCustomDate($nextDate);
            //     break;
        }

        return $nextDate;
    }

    private function getNextCustomDate(Carbon $date): Carbon
    {
        $customDays = $this->repeatData['custom'];
        Log::info('custom days: ' . json_encode($customDays));
        $type = $this->repeatData['type'];
        Log::info('type: ' . $type);
        if (empty($customDays)) {
            throw new Exception('Custom days array is empty');
        }

        $date::setWeekStartsAt(Carbon::MONDAY);

        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $currentDayIndex = $date->dayOfWeek - 1;
        Log::info('start day index: ' . $currentDayIndex . ' day: ' . $daysOfWeek[$currentDayIndex % 7]);
        do {
            $date->addDay();
            $currentDayIndex = $currentDayIndex + 1;
            Log::info('current day index: ' . $currentDayIndex . ' day: ' . $daysOfWeek[$currentDayIndex % 7]);
            if ($currentDayIndex >= 7 && $type === 'biweekly') {
                Log::info('Running out of available days, since we\'re biweekly, adding 7 days');
                $date->addWeek();
            }
            $currentDayIndex = $currentDayIndex % 7;
        } while (!in_array($daysOfWeek[$currentDayIndex], $customDays));

        Log::info('next date: ' . $date->toDateTimeString());

        return $date;
    }
}
