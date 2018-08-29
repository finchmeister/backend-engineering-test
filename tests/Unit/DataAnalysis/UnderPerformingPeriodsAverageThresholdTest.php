<?php

namespace App\Tests\Unit\DataAnalysis;

use App\DataAnalysis\UnderPerformingPeriodsAverageThreshold;
use PHPUnit\Framework\TestCase;

class UnderPerformingPeriodsAverageThresholdTest extends TestCase
{
    /** @var UnderPerformingPeriodsAverageThreshold */
    private $underPerformingPeriodService;

    public function setUp()
    {
        $this->underPerformingPeriodService = new UnderPerformingPeriodsAverageThreshold();
    }

    public function tearDown()
    {
        $this->underPerformingPeriodService = null;
    }

    /**
     * @dataProvider dataProviderGetPeriodsFromDTime
     * @param array $expectedPeriods
     * @param array $dTimes
     */
    public function testGetPeriodsFromDTime(array $expectedPeriods, array $dTimes): void
    {
        $this->assertEquals($expectedPeriods, $this->underPerformingPeriodService->getPeriodsFromDTimes($dTimes));
    }

    public function dataProviderGetPeriodsFromDTime()
    {
        $dTimes = [
            '2018-02-05',
            '2018-02-06',
            '2018-02-07',
        ];

        $periods = [
            0 => ['2018-02-05', '2018-02-07'],
        ];

        yield [$periods, $dTimes];

        $dTimes = [
            '2018-01-29',
            '2018-01-30',
            '2018-01-31',
            '2018-02-06',
            '2018-02-07',
        ];

        $periods = [
            0 => ['2018-01-29', '2018-01-31'],
            1 => ['2018-02-06', '2018-02-07'],
        ];

        yield [$periods, $dTimes];

        $dTimes = [
            '2018-01-29',
        ];

        $periods = [
            0 => ['2018-01-29', '2018-01-29'],
        ];

        yield [$periods, $dTimes];

        $dTimes = [];
        $periods = [];

        yield [$periods, $dTimes];
    }

    /**
     * @dataProvider dataProviderGetUnderPerformingPeriods
     * @param $expectedUnderPerformingPeriods
     * @param $metricData
     */
    public function testGetUnderPerformingPeriods($expectedUnderPerformingPeriods, $metricData): void
    {
        $this->assertEquals(
            $expectedUnderPerformingPeriods,
            $this->underPerformingPeriodService->getUnderPerformingPeriods($metricData)
        );
    }

    public function dataProviderGetUnderPerformingPeriods()
    {
        // No under-performing periods
        $metricData = [
            [
                'metricValue' => 100,
                'dtime' => '2018-01-01',
            ],
            [
                'metricValue' => 100,
                'dtime' => '2018-01-02',
            ],
            [
                'metricValue' => 100,
                'dtime' => '2018-01-03',
            ],
            [
                'metricValue' => 100,
                'dtime' => '2018-01-04',
            ],
        ];

        $expectedUnderPerformingPeriods = [];

        yield [$expectedUnderPerformingPeriods, $metricData];

        // Single under-performing period
        $metricData = [
            [
                'metricValue' => 100,
                'dtime' => '2018-01-01',
            ],
            [
                'metricValue' => 100,
                'dtime' => '2018-01-02',
            ],
            [
                'metricValue' => 100,
                'dtime' => '2018-01-03',
            ],
            [
                'metricValue' => 100,
                'dtime' => '2018-01-04',
            ],
            [
                'metricValue' => 50,
                'dtime' => '2018-01-05',
            ],
            [
                'metricValue' => 50,
                'dtime' => '2018-01-06',
            ],
        ];

        $expectedUnderPerformingPeriods = [
            ['2018-01-05', '2018-01-06']
        ];

        yield [$expectedUnderPerformingPeriods, $metricData];

        // Two under-performing periods
        $metricData = [
            [
                'metricValue' => 100,
                'dtime' => '2018-01-01',
            ],
            [
                'metricValue' => 100,
                'dtime' => '2018-01-02',
            ],
            [
                'metricValue' => 100,
                'dtime' => '2018-01-03',
            ],
            [
                'metricValue' => 100,
                'dtime' => '2018-01-04',
            ],
            [
                'metricValue' => 50,
                'dtime' => '2018-01-05',
            ],
            [
                'metricValue' => 50,
                'dtime' => '2018-01-06',
            ],
            [
                'metricValue' => 100,
                'dtime' => '2018-01-07',
            ],
            [
                'metricValue' => 50,
                'dtime' => '2018-01-08',
            ],
            [
                'metricValue' => 50,
                'dtime' => '2018-01-09',
            ],
            [
                'metricValue' => 50,
                'dtime' => '2018-01-10',
            ],
        ];

        $expectedUnderPerformingPeriods = [
            ['2018-01-05', '2018-01-06'],
            ['2018-01-08', '2018-01-10'],
        ];

        yield [$expectedUnderPerformingPeriods, $metricData];
    }
}
