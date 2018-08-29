<?php


namespace App\DataAnalysis;

use App\Statistics\Statistics;

class UnderPerformingPeriodsAverageThreshold implements UnderPerformingPeriodsInterface
{
    public const UNDER_PERFORMING_THRESHOLD = 0.2;

    public function getUnderPerformingPeriods(array $metricData): array
    {
        $underPerformingDTimes = $this->getUnderPerformingDTimes($metricData);
        return $this->getPeriodsFromDTimes($underPerformingDTimes);
    }

    protected function getUnderPerformingDTimes(array $metricData): array
    {
        $metricValues = array_column($metricData, 'metricValue');
        $metricAverage = Statistics::getMean($metricValues);
        $underPerformingDTimes = [];
        foreach ($metricData as $metricDatum) {
            if ($this->isMetricUnderPerforming($metricDatum['metricValue'], $metricAverage)) {
                $underPerformingDTimes[] = $metricDatum['dtime'];
            }
        }
        return $underPerformingDTimes;
    }

    public function getPeriodsFromDTimes(array $dTimes): array
    {
        $dTimesCount = \count($dTimes);

        if ($dTimes === []) {
            return [];
        }
        $periodI = 0;
        $periodDates = [];
        $periodDates[$periodI][] = $dTimes[0];
        for ($i = 1; $i < $dTimesCount; $i++) {
            if (strtotime($dTimes[$i-1] . '+1 day') === strtotime($dTimes[$i])) {
                // Same period
                $periodDates[$periodI][] = $dTimes[$i];
            } else {
                // New period
                $periodI++;
                $periodDates[$periodI][] = $dTimes[$i];
            }
        }
        $periods = [];
        foreach ($periodDates as $periodDate) {
            $periods[] = [reset($periodDate), end($periodDate)];
        }

        return $periods;
    }

    /**
     * We consider a metric as under-performing if the difference between the overall
     * metric average and the metric value is greater than the metric average * some threshold
     * @param $metricValue
     * @param $metricAverage
     * @return bool
     */
    public function isMetricUnderPerforming(float $metricValue, float $metricAverage): bool
    {
        return ($metricAverage - $metricValue) > $metricAverage * self::UNDER_PERFORMING_THRESHOLD;
    }
}
