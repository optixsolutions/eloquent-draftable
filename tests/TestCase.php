<?php

namespace Optix\Draftable\Tests;

use DateTimeInterface;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/migrations');
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * @param DateTimeInterface|string|null $publishedAt
     * @return TestModel
     */
    protected function newTestModel($publishedAt = null)
    {
        return new TestModel([
            'published_at' => $publishedAt,
        ]);
    }

    /**
     * @param DateTimeInterface|string|null $publishedAt
     * @return TestModel
     */
    protected function createTestModel($publishedAt = null)
    {
        return tap(
            $this->newTestModel($publishedAt),
            function (TestModel $model) {
                $model->save();
            }
        );
    }
}
