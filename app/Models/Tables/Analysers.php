<?php

namespace App\Models\Tables;

use \Illuminate\Support\Facades\DB;

/**
 *
 * @author marcelo moises
 */
class Analysers extends DB {

    const TABLE_NAME = 'analysers';

    public function __construct() {
        parent::__contruct();
    }

    protected static function db()
    {
        return DB::table(self::TABLE_NAME);
    }

    /**
     * @return \Illuminate\Database\Eloquent
     */
    public static function getHeaviest() {
        return self::db()
                ->orderBy('weight', 'desc')
                ->limit(1)
                ->get()
                ->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent
     */
    public static function getFromAnalysisHash($id, $hash) {
        return self::db()
                ->whereRaw("MD5(CONCAT(name, '.', ?)) = ?", [(int) $id, $hash])
                ->get()
                ->first();
    }
}
