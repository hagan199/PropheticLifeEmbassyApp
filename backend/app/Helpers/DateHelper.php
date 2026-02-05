<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    /**
     * Get service types with their schedules
     *
     * @return array
     */
    public static function getServiceTypes(): array
    {
        return [
            'friday_night' => 'Friday Night Service',
            'sunday_service' => 'Sunday Service',
            'midweek' => 'Midweek Service',
        ];
    }

    /**
     * Get next service date by type
     *
     * @param string $serviceType
     * @return Carbon
     */
    public static function getNextServiceDate(string $serviceType): Carbon
    {
        $now = Carbon::now();

        return match ($serviceType) {
            'friday_night' => $now->next(Carbon::FRIDAY)->setTime(19, 0),
            'sunday_service' => $now->next(Carbon::SUNDAY)->setTime(9, 0),
            'midweek' => $now->next(Carbon::WEDNESDAY)->setTime(18, 30),
            default => $now,
        };
    }

    /**
     * Check if date is a service day
     *
     * @param Carbon $date
     * @return bool
     */
    public static function isServiceDay(Carbon $date): bool
    {
        $dayOfWeek = $date->dayOfWeek;

        return in_array($dayOfWeek, [
            Carbon::FRIDAY,    // Friday Night
            Carbon::SUNDAY,    // Sunday Service
            Carbon::WEDNESDAY, // Midweek
        ]);
    }

    /**
     * Get date range for current month
     *
     * @return array
     */
    public static function currentMonthRange(): array
    {
        return [
            'start' => Carbon::now()->startOfMonth(),
            'end' => Carbon::now()->endOfMonth(),
        ];
    }

    /**
     * Get date range for current week
     *
     * @return array
     */
    public static function currentWeekRange(): array
    {
        return [
            'start' => Carbon::now()->startOfWeek(),
            'end' => Carbon::now()->endOfWeek(),
        ];
    }

    /**
     * Format date for display
     *
     * @param Carbon|string $date
     * @param string $format
     * @return string
     */
    public static function formatForDisplay($date, string $format = 'd M Y'): string
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->format($format);
    }

    /**
     * Get human-readable time ago
     *
     * @param Carbon|string $date
     * @return string
     */
    public static function timeAgo($date): string
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->diffForHumans();
    }

    /**
     * Check if date is overdue
     *
     * @param Carbon|string $date
     * @return bool
     */
    public static function isOverdue($date): bool
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->isPast();
    }

    /**
     * Get Ghana timezone
     *
     * @return string
     */
    public static function getTimezone(): string
    {
        return 'Africa/Accra';
    }

    /**
     * Convert to Ghana timezone
     *
     * @param Carbon|string $date
     * @return Carbon
     */
    public static function toGhanaTime($date): Carbon
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->setTimezone(self::getTimezone());
    }
}
