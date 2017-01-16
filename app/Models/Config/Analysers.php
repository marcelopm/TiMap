<?php

namespace App\Models\Config;

/**
 *
 * @author marcelo moises
 */
class Analysers {

    const CONFIG_PATH = 'app.image.analyser';

    /**
     * Get app's analysers configuration @see config/app.php
     *
     * @return type
     */
    protected static function parseConfig() {
        return config(self::CONFIG_PATH);
    }

    /**
     *
     * @return array
     */
    public static function getNames() {
        return array_keys(self::parseConfig());
    }

    /**
     *
     * @return string
     */
    public static function getDefaultName() {
        foreach (self::parseConfig() as $key => $analyse) {
            if (isset($analyse['default']) && $analyse['default'] === true) {
                $name = $key;
                break;
            }
        }

        return $name;
    }
}
