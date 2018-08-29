<?php declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AppAnalyseMetricsCommand
 *
 * @package App\Command
 */
class AppAnalyseMetricsCommand extends Command
{
    public const PRECISION = 2;
    public const UNDER_PERFORMING_THRESHOLD = 0.2;

    /**
     * @var string
     */
    protected static $defaultName = 'app:analyse-metrics';

    /**
     * Configure the command.
     */
    protected function configure(): void
    {
        $this->setDescription('Analyses the metrics to generate a report.');
        $this->addOption('input', null, InputOption::VALUE_REQUIRED, 'The location of the test input');
    }

    /**
     * Detect slow-downs in the data and output them to stdout.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $path = $input->getOption('input');
        $json = file_get_contents($path);
        $jsonData = json_decode($json, true);

        $metricData = $jsonData['data'][0]['metricData'];
        $metricValues = array_column($metricData, 'metricValue');

        $dTimeValues = array_column($metricData, 'dtime');

        $megabitsPerSecondMetrics = array_map([$this, 'convertBytesPerSecondToMegabitsPerSecond'], $metricValues);


        $text = $this->renderOutput(
            $this->getPeriodCheckedFrom($dTimeValues),
            $this->getPeriodCheckedTo($dTimeValues),
            (string)$this->getAverage($megabitsPerSecondMetrics),
            (string)$this->getMin($megabitsPerSecondMetrics),
            (string)$this->getMax($megabitsPerSecondMetrics),
            (string)$this->getMedian($megabitsPerSecondMetrics),
            $this->getUnderPerformingPeriods($metricData)
        );

        $output->write($text);

    }

    /**
     * We consider a metric as under-performing if the difference between the overall
     * metric average and the metric value is greater than the metric average * some threshold
     * @param $metricValue
     * @param $metricAverage
     * @return bool
     */
    protected function isMetricUnderPerforming(float $metricValue, float $metricAverage): bool
    {
        return ($metricAverage - $metricValue) > $metricAverage * self::UNDER_PERFORMING_THRESHOLD;
    }

    protected function getUnderPerformingDTimes(array $metricData)
    {
        // TODO refactor
        $metricValues = array_column($metricData, 'metricValue');
        $metricAverage = $this->getAverage($metricValues);
        $underPerformingDTimes = [];
        foreach ($metricData as $metricDatum) {
            if ($this->isMetricUnderPerforming($metricDatum['metricValue'], $metricAverage)) {
                $underPerformingDTimes[] = $metricDatum['dtime'];
            }
        }
        return $underPerformingDTimes;
    }

    public static function getPeriodsFromDTimes(array $dTimes): array
    {
        $dTimesCount = \count($dTimes);

        if ($dTimes === []) {
            return [];
        }
        $periodI = 0;
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

    public function getUnderPerformingPeriods(array $metricData)
    {
        $underPerformingDTimes = $this->getUnderPerformingDTimes($metricData);
        return self::getPeriodsFromDTimes($underPerformingDTimes);
    }

    /**
     * @param float $bytesPerSecond
     * @return float
     */
    protected function convertBytesPerSecondToMegabitsPerSecond(float $bytesPerSecond)
    {
        return round($bytesPerSecond / 125000, 4);
    }

    /**
     * Here I'm assuming the metric data is always ordered by date
     * @param string[] $dTimeValues
     * @return string
     */
    protected function getPeriodCheckedFrom(array $dTimeValues): string
    {
        return reset($dTimeValues);
    }

    /**
     * Here I'm assuming the metric data is always ordered by date
     * @param string[] $dTimeValues
     * @return string
     */
    protected function getPeriodCheckedTo(array $dTimeValues)
    {
        return end($dTimeValues);
    }

    /**
     * @param float[] $values
     * @return float
     */
    protected function getAverage(array $values): float
    {
        return round(array_sum($values) / \count($values), self::PRECISION);
    }

    /**
     * @param float[] $values
     * @return float
     */
    protected function getMin(array $values): float
    {
        return round(min($values), self::PRECISION);
    }

    /**
     * @param float[] $values
     * @return float
     */
    protected function getMax(array $values)
    {
        return round(max($values), self::PRECISION);
    }

    /**
     * @param float[] $values
     * @return float
     */
    protected function getMedian(array $values): float
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
        return round($median, self::PRECISION);
    }

    protected function renderOutput(
        string $periodCheckedFrom,
        string $periodCheckedTo,
        string $average,
        string $min,
        string $max,
        string $median,
        array $underPerformingPeriods = []
    ) {
        $output = <<<EOT
SamKnows Metric Analyser v1.0.0
===============================

Period checked:

    From: $periodCheckedFrom
    To:   $periodCheckedTo

Statistics:

    Unit: Megabits per second

    Average: $average
    Min: $min
    Max: $max
    Median: $median

EOT;
        if ($underPerformingPeriods !== []) {
            $periodsToInvestigateOutput = '';
            foreach ($underPerformingPeriods as $underPerformingPeriod) {
                $periodsToInvestigateOutput .= <<<EOT
    * The period between $underPerformingPeriod[0] and $underPerformingPeriod[1]
      was under-performing.
EOT;
            }

            $investigateOutput = <<<EOT

Investigate:

$periodsToInvestigateOutput


EOT;
            $output .= $investigateOutput;
        }
        return $output;
    }
}
