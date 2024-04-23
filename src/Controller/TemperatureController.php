<?php

namespace App\Controller;

use App\DTO\StationData\DailyData;
use App\DTO\StationData\MonthlyData;
use App\Entity\Weather;
use App\Helper\DateTimeHelper;
use App\Service\XmlReader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TemperatureController extends AbstractController
{
    #[Route('/temperature', name: 'app_temperature')]
    /**
     * @param XmlReader $xmlReader
     * @return Response
     */
    public function index(XmlReader $xmlReader)
    {
        $xml = $xmlReader->readFile('eng-hourly-07012015-07312015.xml');
        $monthlyData = $xmlReader->getMonthlyData($xml);
        $year = $monthlyData->getYear();
        $month = $monthlyData->getMonth();
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $firstDayOfWeek = DateTimeHelper::getFirstDayOfWeekOfMonth($year, $month);
        // charge les temperatures max, min, moy par jour
        $tempData = $this->getDailyTemperatures($monthlyData);
        // meilleure meteo/temperature pour le festival sur la periode donnée
        $weatherPeriod = $this->sortedWeatherPeriod($monthlyData, 9, 19, 'evening');
        // charge les moyennes par periode de la journée
        $periodData = $this->getPeriodAverageTemperature($monthlyData);

        return $this->render('temperature/index.html.twig', [
            'controller_name' => 'TemperatureController',
            'daysInMonth' => $daysInMonth,
            'currentMonth' => DateTimeHelper::getMonthName($month),
            'currentYear' => $year,
            'firstDayOfWeek' => $firstDayOfWeek,
            'temperatureData' => $tempData,
            'bestDayPeriod' => $weatherPeriod[0]['day'],
            'periodData' => $periodData
        ]);
    }

    /**
     * @param MonthlyData $monthData
     * @return array
     */
    function getDailyTemperatures(MonthlyData $monthData): array
    {
        $dailyData = [];

        foreach ($monthData->getDailyData() as $dayData) {
            $maxTemp = PHP_INT_MIN;
            $minTemp = PHP_INT_MAX;
            $sumTemp = 0;
            $count = 0;
            foreach ($dayData->getHoursData() as $hourData) {
                $maxTemp = max($maxTemp, $hourData->getTemperature());
                $minTemp = min($minTemp, $hourData->getTemperature());
                $sumTemp += $hourData->getTemperature();
                $count++;
            }

            $avgTemp = round($sumTemp / $count);

            $dailyData[] = [
                'day' => $dayData->getDay(),
                'max' => $maxTemp,
                'min' => $minTemp,
                'avg' => $avgTemp
            ];
        }
        return $dailyData;
    }

    /**
     * @param MonthlyData $monthData
     * @return array
     */
    function getPeriodAverageTemperature(MonthlyData $monthData): array
    {
        $periodAvgTemp = [];
        $periodAvgTemp["morning"] = ['count' => 0, 'sum' => 0];
        $periodAvgTemp["afternoon"] = ['count' => 0, 'sum' => 0];
        $periodAvgTemp["evening"] = ['count' => 0, 'sum' => 0];
        $periodAvgTemp["night"] = ['count' => 0, 'sum' => 0];

        foreach ($monthData->getDailyData() as $dayData) {
            foreach ($dayData->getHoursData() as $hourData) {
                $period = DateTimeHelper::getDayPeriod($hourData->getHour());
                $periodAvgTemp[$period]['count']++;
                $periodAvgTemp[$period]['sum'] += $hourData->getTemperature();
            }
        }

        $periodAvgTemp['morning'] = round($periodAvgTemp['morning']['sum'] / $periodAvgTemp['morning']['count']);
        $periodAvgTemp['afternoon'] = round($periodAvgTemp['afternoon']['sum'] / $periodAvgTemp['afternoon']['count']);
        $periodAvgTemp['evening'] = round($periodAvgTemp['evening']['sum'] / $periodAvgTemp['evening']['count']);
        $periodAvgTemp['night'] = round($periodAvgTemp['night']['sum'] / $periodAvgTemp['night']['count']);

        return $periodAvgTemp;
    }

    /**
     * @param DailyData $dailyData
     * @param string $period
     * @return array
     */
    private function getWeather(DailyData $dailyData, string $period): array
    {
        $weather = [];
        $temp = 0;
        $count = 0;
        foreach ($dailyData->getHoursData() as $hourData) {
            if (DateTimeHelper::getDayPeriod($hourData->getHour()) === $period) {
                $temp += $hourData->getTemperature();
                $count++;
                // Si le champ weather comprend plusieurs temps, on prend le premier qui est le 'moins favorable'
                // On pourrait ici calculer directement le score mais en terme de réutilisabilité j'ai plutôt stocker la meteo
                $weather[] = explode(',', $hourData->getWeather())[0];
            }
        }
        return [
            'avgTemp' => round($temp / $count),
            'weather' => $weather
        ];
    }

    /**
     *
     * Pour calculer le meilleur jour pour assister à un spectacle j'ai écrit laa fonction sortedWeatherPeriod
     * On donne en paramètre le permier jour et le dernier jour du festival (par facilité vis à vis du test j'ai considéré que c'était une
     * période sur un mois uniaue et non une période à cheval sur 2 mois).
     *
     * Pour chaque jour et pour la période précisée (matin, après-midi,...) la function getWeather va retourner la température moyenne
     * sur la période et la liste meteo pour chaque heure.
     * Chaque statut meteo correspond a un score dans l'entité Weather
     * On fait la somme des scores et le resultat renvoie pour une liste des jours avec la temperature moyenne et le score total trié par
     * score (le plus haut en premier) et par temperature moyenne (la plus haute en premier)
     *
     * Le jour le plus favorable sera donc le premier jour de la liste
     *
     */

    /**
     * @param MonthlyData $monthlyData
     * @param int $startDay
     * @param int $endDay
     * @param string $period
     * @return array
     */
    private function sortedWeatherPeriod(MonthlyData $monthlyData, int $startDay, int $endDay, string $period): array
    {
        $weatherPeriod = [];
        for ($i = $startDay - 1; $i < $endDay; $i++) {
            $dayWeather = $this->getWeather($monthlyData->getDailyData()[$i], $period);
            $score = 0;
            foreach ($dayWeather['weather'] as $weather) {
                $score += Weather::WEATHER_SCORE[$weather];
            }
            $weatherPeriod[] = ['day' => $i + 1, 'avgTemp' => $dayWeather['avgTemp'], 'score' => $score];
        }
        usort($weatherPeriod, function($a, $b) {
            if ($a['score'] != $b['score']) {
                return $b['score'] - $a['score']; // Plus haut score en premier
            }
            return $b['avgTemp'] - $a['avgTemp']; // A score égal on trie par temperature la plus élevée
        });
        return $weatherPeriod;
    }
}
