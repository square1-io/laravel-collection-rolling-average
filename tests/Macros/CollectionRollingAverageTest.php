<?php

namespace Square1\CollectionRollingAverage\Test\Macros;

use Illuminate\Support\Collection;
use Square1\CollectionRollingAverage\Test\TestCase;

class CollectionRollingAverageTest extends TestCase
{
    /** @test */
    public function rollling_average_is_calculated_without_limit()
    {
        $data = new Collection([1, 2, 3]);

        $averages = new Collection([1, 1.5, 2]);

        $this->assertEquals($averages, $data->rollingAverage());
    }

    /** @test */
    public function rollling_average_is_calculated_with_lookback_limit()
    {
        $data = new Collection([1, 2, 3, 4, 5, 6]);

        // Looking back over last 2 entries, averages should be:
        $averages = new Collection([
            1,      // 1
            1.5,    // 1+2 / 2
            2.5,    // 2+3 / 2
            3.5,    // 3+4 / 2
            4.5,    // 4+5 / 2
            5.5     // 5+6 / 2
        ]);

        $this->assertEquals($averages, $data->rollingAverage(2));
    }

    /** @test */
    public function rollling_average_is_calculated_with_lookback_limit_larger_than_set()
    {
        $data = new Collection([1, 2, 3]);

        // Lookback is 5, larger than whole set, so we expect it be the same as no lookback being applied
        $averages = new Collection([1, 1.5, 2]);

        $this->assertEquals($averages, $data->rollingAverage(5));
    }

    /** @test */
    public function enforced_lookback_returns_smaller_set()
    {
        $data = new Collection([1, 2, 3, 4, 5, 6]);

        // Looking back over last 2 entries, averages should be:
        $averages = new Collection([
            2,    // 1+2+3 / 3
            3,    // 2+3+4 / 3
            4,    // 3+4+5 / 3
            5,    // 4+5+6 / 3
        ]);

        $this->assertEquals($averages, $data->rollingAverage(3, $enforceLookback = true));
    }

    /** @test */
    public function enforced_lookback_larger_than_set_returns_empty_collection()
    {
        $data = new Collection([1, 2, 3]);

        $this->assertEquals(
            new Collection([]),
            $data->rollingAverage($data->count() + 1, $enforceLookback = true)
        );
    }


    /** @test */
    public function rollling_average_applies_weights_when_weight_size_is_less_than_collection_size()
    {
        $data = new Collection([1, 2, 2, 4, 5, 6]);
        $weights = new Collection([5, 2]);

        $averages = new Collection([
            5,      // 1 * 5
            6,      // (2 * 5) + (1 * 2) / 2
            5,      // (2 * 5) + (2 * 2) + 1 / 3
            6.75,   // (4 * 5) + (2 * 2) + 2 + 1 / 4
            9.25,   // (5 * 5) + (4 * 2) + 2 + 2 / 4
            11.5,   // (6 * 5) + (5 * 2) + 4 + 2 / 4
        ]);

        $this->assertEquals($averages, $data->rollingAverage(4, false, $weights));
    }


    /** @test */
    public function rollling_average_applies_weights_when_weight_size_is_larger_than_collection_size()
    {
        $data = new Collection([1, 2, 3]);
        $weights = new Collection([5, 2, 2, 2]);

        $averages = new Collection([
            5,      // 1 * 5
            6,      // (2 * 5) + (1 * 2) / 2
            7,      // (3 * 5) + (2 * 2) + (1 * 2) / 3
        ]);

        $this->assertEquals($averages, $data->rollingAverage(4, false, $weights));
    }


    /** @test */
    public function rollling_average_applies_weights_when_weight_size_is_less_than_collection_size_and_lookback_is_applied()
    {
        $data = new Collection([1, 2, 2, 4, 5, 6]);
        $weights = new Collection([5, 2]);

        $averages = new Collection([
            6.75,   // (4 * 5) + (2 * 2) + 2 + 1 / 4
            9.25,   // (5 * 5) + (4 * 2) + 2 + 2 / 4
            11.5,   // (6 * 5) + (5 * 2) + 4 + 2 / 4
        ]);

        $this->assertEquals($averages, $data->rollingAverage(4, true, $weights));
    }


    /** @test */
    public function rollling_average_applies_weights_when_weight_size_is_larger_than_collection_size_and_lookback_is_applied()
    {
        $data = new Collection([1, 2, 3, 1, 2, 3]);
        $weights = new Collection([5, 2, 2, 2, 3, 4]);

        $averages = new Collection([
            7,      // (3 * 5) + (2 * 2) + (1 * 2) / 3
            5,      // (1 * 5) + (3 * 2) + (2 * 2) / 3
            6,      // (2 * 5) + (1 * 2) + (3 * 2) / 3
            7,      // (3 * 5) + (2 * 2) + (1 * 2) / 3
        ]);

        $this->assertEquals($averages, $data->rollingAverage(3, true, $weights));
    }
}
