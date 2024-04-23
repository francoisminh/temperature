<?php


namespace App\DTO\StationData;


class DailyData
{
    private int $day;
    private array $hoursData = [];

    public function __construct(int $day)
    {
        $this->day = $day;

    }

    /**
     * @param HourlyData $hourData
     * @return void
     */
    public function addHourData(HourlyData $hourData): void
    {
        $this->hoursData[] = $hourData;
    }

    /**
     * @return int
     */
    public function getDay(): int
    {
        return $this->day;
    }

    /**
     * @return array<HourlyData>
     */
    public function getHoursData(): array
    {
        return $this->hoursData;
    }

}