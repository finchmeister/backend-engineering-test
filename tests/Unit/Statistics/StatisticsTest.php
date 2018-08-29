<?php

namespace App\Tests\Unit\Statistics;

use App\Statistics\Statistics;
use PHPUnit\Framework\TestCase;

/**
 * @link https://github.com/markrogoyski/math-php/blob/master/tests/Statistics/AverageTest.php
 * Class StatisticsTest
 * @package App\Tests\Unit\Statistics
 */
class StatisticsTest extends TestCase
{
    /**
     * @testCase     mean
     * @dataProvider dataProviderGetMean
     * @param        array $numbers
     * @param        float $mean
     */
    public function testMean(array $numbers, float $mean)
    {
        $this->assertEquals($mean, Statistics::getMean($numbers), '', 0.01);
    }

    /**
     * @return array [numbers, mean]
     */
    public function dataProviderGetMean(): array
    {
        return [
            [ [ 1, 1, 1 ], 1 ],
            [ [ 1, 2, 3 ], 2 ],
            [ [ 2, 3, 4 ], 3 ],
            [ [ 5, 5, 6 ], 5.33 ],
            [ [ 13, 18, 13, 14, 13, 16, 14, 21, 13 ], 15 ],
            [ [ 1, 2, 4, 7 ], 3.5 ],
            [ [ 8, 9, 10, 10, 10, 11, 11, 11, 12, 13 ], 10.5 ],
            [ [ 6, 7, 8, 10, 12, 14, 14, 15, 16, 20 ], 12.2 ],
            [ [ 9, 10, 11, 13, 15, 17, 17, 18, 19, 23 ], 15.2 ],
            [ [ 12, 14, 16, 20, 24, 28, 28, 30, 32, 40 ], 24.4 ],
            [ [1.1, 1.2, 1.3, 1.3, 1.4, 1.5 ], 1.3 ],
        ];
    }

    /**
     * @testCase     median
     * @dataProvider dataProviderGetMedian
     * @param        array $numbers
     * @param        float $median
     */
    public function testMedian(array $numbers, float $median)
    {
        $this->assertEquals($median, Statistics::getMedian($numbers), '', 0.01);
    }

    /**
     * @return array [numbers, median]
     */
    public function dataProviderGetMedian(): array
    {
        return [
            [ [ 1, 1, 1 ], 1 ],
            [ [ 1, 2, 3 ], 2 ],
            [ [ 2, 3, 4 ], 3 ],
            [ [ 5, 5, 6 ], 5 ],
            [ [ 1, 2, 3, 4, 5 ], 3 ],
            [ [ 1, 2, 3, 4, 5, 6 ], 3.5 ],
            [ [ 13, 18, 13, 14, 13, 16, 14, 21, 13 ], 14 ],
            [ [ 1, 2, 4, 7 ], 3 ],
            [ [ 8, 9, 10, 10, 10, 11, 11, 11, 12, 13 ], 10.5 ],
            [ [ 6, 7, 8, 10, 12, 14, 14, 15, 16, 20 ], 13 ],
            [ [ 9, 10, 11, 13, 15, 17, 17, 18, 19, 23 ], 16 ],
            [ [ 12, 14, 16, 20, 24, 28, 28, 30, 32, 40 ], 26 ],
            [ [1.1, 1.2, 1.3, 1.4, 1.5 ], 1.3 ],
            [ [1.1, 1.2, 1.3, 1.3, 1.4, 1.5 ], 1.3 ],
            [ [1.1, 1.2, 1.3, 1.4 ], 1.25 ],
        ];
    }

}
