<?php

namespace Square1\CollectionRollingAverage\Test;

use PHPUnit\Framework\TestCase as BaseTestCase;
use ReflectionClass;
use Square1\CollectionRollingAverage\Providers\CollectionRollingAverageServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $this->createDummyprovider()->register();
    }

    protected function createDummyprovider(): CollectionRollingAverageServiceProvider
    {
        $reflectionClass = new ReflectionClass(CollectionRollingAverageServiceProvider::class);

        return $reflectionClass->newInstanceWithoutConstructor();
    }
}
