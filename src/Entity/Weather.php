<?php


namespace App\Entity;


class Weather
{
    // Score météo arbitrairement fixé pour déterminer le meilleur score météo
    const WEATHER_SCORE = [
        'Clear' => 2,
        'Mainly Clear' => 1,
        'NA' => 0,
        'Mostly Cloudy' => -1,
        'Cloudy' => -1,
        'Haze' => -1,
        'Fog' => -1,
        'Drizzle' => -2,
        'Rain' => -2,
        'Moderate Rain Showers' => -3,
        'Rain Showers' => -4,
        'Thunderstorms' => -5
    ];
}