<?php

namespace Square1\CollectionRollingAverage\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class CollectionRollingAverageServiceProvider extends ServiceProvider
{
    public function register()
    {
        /**
         * Rolling average macro.
         *
         * @param int $recordsToAverage  How many records to look back when creating the average
         * @param bool $enforceLookback  Only average $recordsToAverage values, i.e. if asked to average 5 entries,
         *                               don't attempt to average anything until the 5th entry.
         * @param Collection $weightings Weightings to apply to values. This allows different importance
         *                               to be applied to different values. First value is applied to most
         *                               recent addition to rolling average.
         */
        Collection::macro('rollingAverage', function (int $recordsToAverage = null, bool $enforceLookback = false, Collection $weights = null) {
            if ($recordsToAverage === null
                || ($recordsToAverage > $this->count() && !$enforceLookback)) {
                $recordsToAverage = $this->count();
            }
            // The number of weights shouldn't exceed our recordsToAverage - trim it down if so.
            if ($weights && $weights->count() > $recordsToAverage) {
                $weights = $weights->slice(0, $recordsToAverage);
            }

            // Asking for a forced lookback, averaging over more records than we have? Return empty set now.
            if ($enforceLookback && $recordsToAverage > $this->count()) {
                return collect([]);
            }

            $rolling = collect([]);
            $averages = collect([]);

            foreach ($this as $index => $value) {
                $weightedAverage = collect([]);

                // First item by first weight
                // Set should be this item, and X preceding
                // For each in set, multiply by available weight
                if ($weights) {
                    $startingPoint = max(0, ($index+1) - $recordsToAverage);
                    $recordsToTake = min($index+1, $recordsToAverage);
                    $weighted = $this->slice($startingPoint, $recordsToTake)->reverse();

                    foreach ($weighted->values() as $weightIndex => $item) {
                        if (!empty($weights[$weightIndex])) {
                            $weightedAverage->prepend($weights[$weightIndex] * $item);
                        } else {
                            $weightedAverage->prepend($item);
                        }
                    }
                    $averages->push($weightedAverage->average());
                } else {
                    // Simple average - build up a running list, and slice off the amount we need for our average
                    $rolling = $rolling->prepend($value)
                        ->slice(0, $recordsToAverage);

                    $averages->push($rolling->average());
                }
            }

            // Enforced lookback means that we ignore the first N entries ($recordsToAverage - 1, due to 0-indexing)
            if ($enforceLookback) {
                $averages = $averages->slice(($recordsToAverage - 1))->values();
            }

            return $averages;
        });

    }
}
