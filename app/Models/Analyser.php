<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @author marcelo moises
 */
class Analyser extends Model {

    const CONFIG_PATH = 'app.image.analyser';

    protected $fillable = [
        'name',
        'weight'
    ];

    /**
     * Add one from current weight
     */
    public function incrementWeight() {
        self::increment('weight');
    }

    /**
     * Sub one from current weight
     */
    public function decrementWeight() {
        self::decrement('weight');
    }

    /**
     * @return \Illuminate\Database\Eloquent
     */
    public static function getHeaviest() {
        return self::orderBy('weight', 'desc')
                        ->limit(1)
                        ->get()
                        ->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent
     */
    public static function getFromAnalysisHash($id, $hash) {
        return self::whereRaw("MD5(CONCAT(name, '.', ?)) = ?", [(int) $id, $hash])
                        ->get()
                        ->first();
    }

}
