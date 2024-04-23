<?php


namespace App\DTO\StationData;


class MonthlyData
{
    private int $year;
    private int $month;
    private array $dailyData = [];

    public function __construct(int $year, int $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    /**
     * @return int
     */
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * @return int
     */
    public function getMonth(): int
    {
        return $this->month;
    }

    /**
     * @return array<DailyData>
     */
    public function getDailyData(): array
    {
        return $this->dailyData;
    }

    /**
     * @param DailyData $data
     * @return void
     */
    public function addDailyData(DailyData $data): void
    {
        $this->dailyData[] = $data;
    }

}