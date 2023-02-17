# Add Rolling Average Functionality To Collections

This package adds a rolling average functionality to Laravel's `Collection` facade. Rolling averages (also known as moving averages) are a statistic to capture the average change in a data range over time. For example, looking at a graph of daily temperature, or stock market movement, the graph may appear very noisy and erratic. The graph can be smoothed out to show longer-term trends by replacing each daily data point with an average of the previous N days.

![Rolling Average graph](https://github.com/square1-io/laravel-collection-rolling-average/blob/main/average-graph.png?raw=true)

Take this example, where we want the rolling average of the last 5 days
| Day | Value | Rolling Average |
| --- | ----- | --------------- |
| 1 | 5 | 5 |
| 2 | 7 | 6 |
| 3 | 3 | 5 |
| 4 | 5 | 5 |
| 5 | 10 | 6 |
| 6 | 3 | 5.6 |
| 7 | 5 | 5.2 |

This is a rolling average of all values in the set. While we want the rolling average of the latest 5 in this example, where there are less than 5 entries, the average is over the available entries.

It's possible to force this function to be more strict, and only start calculating the average once we have the minimum number of entries we want (5, in this case). This would mean that the resulting data changes a bit:
| Day | Value | Rolling Average |
| --- | ----- | --------------- |
| 1 | 5 | - |
| 2 | 7 | - |
| 3 | 3 | - |
| 4 | 5 | - |
| 5 | 10 | 6 |
| 6 | 3 | 5.6 |
| 7 | 5 | 5.2 |



## Install

Via Composer

``` bash
$ composer require square1/laravel-collection-rolling-average
```

The package will be automatically registered.


## Usage

### Rolling average
This will show an average of all entries to date. Each returned value shall be the average of all values up to that point in the collection. This can mean that the early values retain some of their "noisiness", before the average really kicks in.

```php
    $data = new Collection([1, 2, 3, 4, 5]);

    $averages = $data->rollingAverage();
    /**
     *   1,      // 1
     *   1.5,    // 1+2 / 2 (only 2 entries seen so far)
     *   2,      // 1+2+3 / 3
     *   2.5,    // 1+2+3+4 / 4
     *   3       // 1+2+3+4+5 / 5
     */
```


### Rolling average with a limit
Calculate the rolling average based on the N previous entries to each data point.
The collection returned will be the same size as the one supplied. This means that for positions in the collection before N values have been seen, the rolling average is the average of all values seen to date. If you want to exclude all values before N entries, take a look at the [Rolling average with limited lookback](#rolling-average-with-limited-lookback) section.

```php
    $data = new Collection([1, 2, 3, 4, 5, 6]);
    // How many entries should we consider in our average?
    $lookback = 2;
    $averages = $data->rollingAverage($lookback);
    /**
     * Looking back over last 2 entries, averages should be:
     *   1,      // 1
     *   1.5,    // 1+2 / 2
     *   2.5,    // 2+3 / 2
     *   3.5,    // 3+4 / 2
     *   4.5,    // 4+5 / 2
     *   5.5     // 5+6 / 2
     */
```

### Rolling average with limited lookback
By default the package will return a collection with the same number of values supplied to it. This can lead to some noisiness at the start of the collection. For example, if we hav a dataset of 30 entries, and want a rolling average over the last 5 values, the first 4 values will be averages of the first N values, where N < 5. To avoid this, the parameter `$includeAll` can be set to `false`. This will return a collection with fewer results than the original. This will remove the noisiness from the start of the collection, but will mean a mismatch in the number of values returned.

``` php
    $data = new Collection([1, 2, 3, 4, 5, 6]);
    $averages = $data->rollingAverage(4, $enforceLimitedLookback = true);

    /**
     * 2.5,
     * 3.5,
     * 4.5
     */
```

| Original | Average (default) | Average (limited lookback) |
| -------- | ------------------ | ------------------------- |
| 1 | 1 | - |
| 2 | 1.5 | - |
| 3 | 2 | - |
| 4 | 2.5 | 2.5 |
| 5 | 3.5 | 3.5 |
| 6 | 4.5 | 4.5 |



### Apply weightings
Add weightings to increase or decrease the relevance of the most recent entries.

``` php
    $data = new Collection([1, 2, 2, 4, 5, 6]);
    // Weights will be applied in the order of current data point N, then N-1, and so on.
    // Where no weighting exists, a default of 1 is used.
    $weights = new Collection([5, 2]);

    $averages = $data->rollingAverage(4, $enforceLimitedLookback = false, $weights);

    /**
      * collect([
      *   5,      // 1 * 5
      *   6,      // (2 * 5) + (1 * 2) / 2
      *   5,      // (2 * 5) + (2 * 2) + 1 / 3
      *   6.75,   // (4 * 5) + (2 * 2) + 2 + 1 / 4
      *   9.25,   // (5 * 5) + (4 * 2) + 2 + 2 / 4
      *   11.5,   // (6 * 5) + (5 * 2) + 4 + 2 / 4
      * ]);
      */
```

## Tests
```bash
$ composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
