<?php

namespace App\Manager;

class DateManager
{
    public static function isWeekend(string $date): bool
    {
        return (date('N', strtotime($date)) >= 6);
    }

    public static function addDays(string $date, int $days): string
    {
        $d = getdate(strtotime($date));
        return date('Y-m-d', mktime(0, 0, 0, $d['mon'], $d['mday']+$days, $d['year']));
    }
}
