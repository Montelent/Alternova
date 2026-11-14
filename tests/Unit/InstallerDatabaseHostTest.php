<?php

namespace Tests\Unit;

use App\Support\Installer;
use PHPUnit\Framework\TestCase;

class InstallerDatabaseHostTest extends TestCase
{
    public function test_postgresql_accepts_an_existing_absolute_socket_directory(): void
    {
        self::assertTrue(Installer::isValidDatabaseHost('pgsql', '/tmp'));
    }

    public function test_database_host_validation_rejects_socket_paths_for_mysql_and_traversal(): void
    {
        self::assertFalse(Installer::isValidDatabaseHost('mysql', '/tmp'));
        self::assertFalse(Installer::isValidDatabaseHost('pgsql', '/tmp/../etc'));
        self::assertFalse(Installer::isValidDatabaseHost('pgsql', '/tmp;host=attacker'));
    }

    public function test_database_host_validation_keeps_normal_hostnames(): void
    {
        self::assertTrue(Installer::isValidDatabaseHost('pgsql', 'db.example.com'));
        self::assertTrue(Installer::isValidDatabaseHost('mysql', '127.0.0.1'));
    }
}
