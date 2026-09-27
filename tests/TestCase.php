<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertDatabaseIsIsolated();
    }

    /**
     * Refuse to run any test against a real database.
     *
     * Tests that use RefreshDatabase run migrate:fresh, which drops every table.
     * Pointed at a live connection that silently destroys the data, so fail loudly instead.
     */
    private function assertDatabaseIsIsolated(): void
    {
        $connection = config('database.default');
        $driver     = config("database.connections.{$connection}.driver");
        $database   = config("database.connections.{$connection}.database");

        if ($driver !== 'sqlite' || ! in_array($database, [':memory:', 'file::memory:'], true)) {
            throw new \RuntimeException(
                "Test diblokir: koneksi '{$connection}' memakai driver '{$driver}' "
                . "dengan database '{$database}', bukan sqlite in-memory. "
                . 'Menjalankan test pada koneksi ini akan menghapus database.'
            );
        }
    }
}
