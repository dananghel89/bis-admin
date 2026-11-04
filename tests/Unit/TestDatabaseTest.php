<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class TestDatabaseTest extends TestCase
{
    /**
     * Tests must never run against the real MySQL database.
     *
     * @return void
     */
    public function test_tests_use_an_in_memory_sqlite_database()
    {
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
    }
}
