<?php

namespace App\Tests\Unit;


use App\Command\AppAnalyseMetricsCommand;
use PHPUnit\Framework\TestCase;

class Test extends TestCase
{

    /**
     * @dataProvider dataProviderGetPeriodsFromDTime
     * @param array $expectedPeriods
     * @param array $dTimes
     */
    public function testGetPeriodsFromDTime(array $expectedPeriods, array $dTimes)
    {
        $this->assertEquals($expectedPeriods, AppAnalyseMetricsCommand::getPeriodsFromDTimes($dTimes));
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
}
