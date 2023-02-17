<?php

namespace Square1\CollectionRollingAverage\Test;

use Orchestra\Testbench\TestCase as Orchestra;
use Square1\CollectionRollingAverage\Providers\CollectionRollingAverageServiceProvider;

abstract class IntegrationTestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [CollectionRollingAverageServiceProvider::class];
    }
}
