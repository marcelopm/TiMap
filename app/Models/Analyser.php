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
}
