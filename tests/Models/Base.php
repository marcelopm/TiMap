<?php

namespace Tests\Models;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;

class Base extends TestCase {

    const SQLITE = 'sqlite';

    use DatabaseMigrations;

    public function setUp() {
        if (self::isDriverSQLite()) {
            self::createDB();
        }

        parent::setUp();

        if (self::isDriverSQLite()) {
            self::deleteDB();
        }
    }

    protected function isDriverSQLite() {
        return ENV('DB_CONNECTION', '') === self::SQLITE;
    }

//    public function tearDown() {
//        parent::tearDown();
//
//        if (ENV('DB_CONNECTION', '') === self::SQLITE) {
//            self::cleanTests();
//        }
//    }

    public function createDB() {
        $database = ENV('DB_DATABASE', '');

        // Create an sqlite db file
        if (!file_exists($database)) {
            touch($database);
        }
    }

    public function deleteDB() {
        $database = ENV('DB_DATABASE', '');

        // Remove sqlite db file
        if (file_exists($database)) {
            unlink($database);
        }
    }

    public function setUpDB() {
        $PDO = DB::connection()->getPdo();

        // Create an MySQL like CONCAT function
        $PDO->sqliteCreateFunction('CONCAT', function (string ...$args) {
            $concatenated = current($args);
            while ($arg = next($args)) {
                $concatenated .= $arg;
            }

            return $concatenated;
        });

        // Create an MySQL like MD5 function
        $PDO->sqliteCreateFunction('MD5', function (string $string) {
            return md5($string);
        });
    }

}
