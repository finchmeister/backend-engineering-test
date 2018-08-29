<?php


namespace App\DataAnalysis;

interface UnderPerformingPeriodsInterface
{
    /**
     * @param array $metricData
     * @return array
     */
    public function getUnderPerformingPeriods(array $metricData): array;
}