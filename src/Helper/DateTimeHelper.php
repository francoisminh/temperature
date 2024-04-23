<?php


namespace App\Helper;


class DateTimeHelper
{
    public static function getFirstDayOfWeekOfMonth($year, $month) {
        $firstDayOfMonthTimestamp = strtotime("$year-$month-01");
        // renvoie le jour de la semaine pour le premier jour du mois (0 = dimanche,...)
        return date('w', $firstDayOfMonthTimestamp);
    }

    /**
     * @param $monthNumber
     * @return string
     */
    public static function getMonthName($monthNumber): string
    {
        setlocale(LC_TIME, 'fr_FR.utf8');
        return strftime('%B', mktime(0, 0, 0, $monthNumber, 1));
    }

    /**
     * @param $hour
     * @return string
     */
    public static function getDayPeriod($hour): string
    {
        if ($hour >= 5 && $hour < 12) {
            return "morning";
        }
        if ($hour >= 12 && $hour < 17) {
            return "afternoon";
        }
        if ($hour >= 17 && $hour < 21) {
            return "evening";
        }
        return "night";
    }
}