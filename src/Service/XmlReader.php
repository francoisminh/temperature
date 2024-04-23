<?php
namespace App\Service;

use App\DTO\StationData\DailyData;
use App\DTO\StationData\HourlyData;
use App\DTO\StationData\MonthlyData;
use SimpleXMLElement;

class XmlReader
{
    /**
     * @param string $filePath
     * @return SimpleXMLElement
     */
    public function readFile(string $filePath): SimpleXMLElement
    {
        $xmlData = file_get_contents($filePath);
        return simplexml_load_string($xmlData);
    }

    public function getMonthlyData(SimpleXMLElement $xml): MonthlyData
    {
        $index = 0;
        $year = intval($xml->stationdata[$index]['year']);
        $month = intval($xml->stationdata[$index]['month']);
        $currentDay = 1;
        $dayData = new DailyData(1);
        $monthlyData = new MonthlyData($year, $month);
        do {
            $day = intval($xml->stationdata[$index]['day']);
            $hour = intval($xml->stationdata[$index]['hour']);
            $temp = round((float)$xml->stationdata[$index]->temp);
            $weather = $xml->stationdata[$index]->weather ?? 'NA';
            // Si on change de jour on ajoute les dailyData
            if ($day > $currentDay) {
                $monthlyData->addDailyData($dayData);
                $dayData = new DailyData($day);
                $currentDay = $day;
            }
            $hourData = new HourlyData($hour, $temp, $weather);
            $dayData->addHourData($hourData);
            $index++;
        }
        while ($index < count($xml->stationdata));
        $monthlyData->addDailyData($dayData);
        return $monthlyData;
    }
}