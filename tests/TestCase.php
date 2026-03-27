<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') === 'sqlite') {
            $database = config('database.connections.sqlite.database');

            if (is_string($database) && $database !== ':memory:' && ! file_exists($database)) {
                touch($database);
            }
        }

        $this->withoutVite();
    }
}
