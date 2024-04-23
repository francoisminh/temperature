<?php


namespace App\DTO\StationData;


class HourlyData
{
    private int $hour;
    private int $temperature;
    private string $weather;

    public function __construct(int $hour, int $temperature, string $weather)
    {
        $this->hour = $hour;
        $this->temperature = $temperature;
        $this->weather = $weather;
    }

    /**
     * @return int
     */
    public function getHour(): int
    {
        return $this->hour;
    }

    /**
     * @return int
     */
    public function getTemperature(): int
    {
        return $this->temperature;
    }

    /**
     * @return string
     */
    public function getWeather(): string
    {
        return $this->weather;
    }

}