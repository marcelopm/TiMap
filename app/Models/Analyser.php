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
     * @return \Illuminate\Database\Eloquent
     */
    public static function getHeaviest() {
        return self::orderBy('weight', 'desc')->limit(1)->get()->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent
     */
    public static function getFromAnalysisHash($id, $hash) {
        return self::whereRaw("MD5(CONCAT(name,'.',?)) = ?", [(int) $id, $hash])
                ->get()
                ->first();
    }

    /**
     * Get app's analysers configuration @see config/app.php
     *
     * @return type
     */
    public static function getConfig() {
        return config(self::CONFIG_PATH);
    }

    /**
     *
     * @return array
     */
    public static function getNameListFromConfig() {
        return array_keys(self::getConfig());
    }

    /**
     *
     * @return string
     */
    public static function getDefaultName() {
        foreach (self::getConfig() as $key => $analyse) {
            if (isset($analyse['default']) && $analyse['default'] === true) {
                $name = $key;
                break;
            }
        }

        return $name;
    }
}
