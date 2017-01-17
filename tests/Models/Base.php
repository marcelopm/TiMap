<?php

namespace Tests\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;

class Base extends TestCase {

    const SQLITE = 'sqlite';

    use DatabaseMigrations;

    public function setUp() {
        parent::setUp();

        if (self::isDBDriverSQLite()) {
            self::setUpDB();
        }
    }

    protected function isDBDriverSQLite() {
        return ENV('DB_CONNECTION', '') === self::SQLITE;
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
