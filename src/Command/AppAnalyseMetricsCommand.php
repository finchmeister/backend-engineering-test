<?php declare(strict_types=1);

namespace App\Command;

use App\DataAnalysis\UnderPerformingPeriodsInterface;
use App\Statistics\Statistics;
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
    public const DISPLAY_PRECISION = 2;

    /**
     * @var string
     */
    protected static $defaultName = 'app:analyse-metrics';
    /**
     * @var UnderPerformingPeriodsInterface
     */
    private $underPerformingPeriodService;

    public function __construct(
        UnderPerformingPeriodsInterface $underPerformingPeriodService
    ) {
        $this->underPerformingPeriodService = $underPerformingPeriodService;
        parent::__construct();
    }

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
        $data = $this->getDataFromInput($input);

        $dTimeValues = $this->getDTimeValues($data);
        $megabitsPerSecondMetrics = $this->getMegabitsPerSecondMetrics($data);

        $underPerformingPeriods = $this->underPerformingPeriodService->getUnderPerformingPeriods(
            $this->getMetricData($data)
        );

        $output->write($this->renderOutput(
            $this->getPeriodCheckedFrom($dTimeValues),
            $this->getPeriodCheckedTo($dTimeValues),
            Statistics::getMean($megabitsPerSecondMetrics),
            Statistics::getMin($megabitsPerSecondMetrics),
            Statistics::getMax($megabitsPerSecondMetrics),
            Statistics::getMedian($megabitsPerSecondMetrics),
            $underPerformingPeriods
        ));
    }

    protected function getDataFromInput(InputInterface $input): array
    {
        $path = $input->getOption('input');
        $json = file_get_contents($path);
        return json_decode($json, true);
    }

    /**
     * Renders the summary of results
     * @param string $periodCheckedFrom
     * @param string $periodCheckedTo
     * @param float $average
     * @param float $min
     * @param float $max
     * @param float $median
     * @param array $underPerformingPeriods
     * @return string
     */
    protected function renderOutput(
        string $periodCheckedFrom,
        string $periodCheckedTo,
        float $average,
        float $min,
        float $max,
        float $median,
        array $underPerformingPeriods = []
    ) {
        $average = round($average, self::DISPLAY_PRECISION);
        $min = round($min, self::DISPLAY_PRECISION);
        $max = round($max, self::DISPLAY_PRECISION);
        $median = round($median, self::DISPLAY_PRECISION);

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

    protected function getDTimeValues(array $data): array
    {
        return array_column($this->getMetricData($data), 'dtime');
    }

    protected function getMetricValues(array $data): array
    {
        return array_column($this->getMetricData($data), 'metricValue');
    }

    protected function getMegabitsPerSecondMetrics(array $data)
    {
        return array_map([$this, 'convertBytesPerSecondToMegabitsPerSecond'], $this->getMetricValues($data));
    }

    /**
     * @param float $bytesPerSecond
     * @return float
     */
    protected function convertBytesPerSecondToMegabitsPerSecond(float $bytesPerSecond): float
    {
        return round($bytesPerSecond / 125000, 4);
    }

    protected function getMetricData(array $data): array
    {
        return $data['data'][0]['metricData'];
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
    protected function getPeriodCheckedTo(array $dTimeValues): string
    {
        return end($dTimeValues);
    }
}
