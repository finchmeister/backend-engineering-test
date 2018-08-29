<?php


namespace App\Statistics;


class Statistics
{

    /**
     * @param float[] $values
     * @return float
     */
    public static function getMean(array $values): float
    {
        return array_sum($values) / \count($values);
    }

    /**
     * @param float[] $values
     * @return float
     */
    public static function getMin(array $values): float
    {
        return min($values);
    }

    /**
     * @param float[] $values
     * @return float
     */
    public static function getMax(array $values): float
    {
        return max($values);
    }

    /**
     * @param float[] $values
     * @return float
     */
    public static function getMedian(array $values): float
    {
        $values = array_values($values);
        sort($values);
        $middleIndex = (int)floor((count($values) - 1) / 2);
        if (\count($values) % 2 === 0) {
            $lowIndex = $middleIndex;
            $highIndex = $lowIndex + 1;
            $median = ($values[$lowIndex] + $values[$highIndex]) / 2;
        } else {
            $median = $values[$middleIndex];
        }
        return $median;
    }

}