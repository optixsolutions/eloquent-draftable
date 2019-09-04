<?php

namespace Optix\Draftable\Tests;

use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => ''
        ]);
    }

    protected function setUpDatabase($app)
    {
        $app['db']->connection()
            ->getSchemaBuilder()
            ->create('test_models', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('title');
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
            });
    }
}
