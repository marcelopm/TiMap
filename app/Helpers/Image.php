<?php

namespace App\Helpers;

/**
 *
 * @author marcelo moises
 */
class Image {

    /**
     * Create a md5 hash for given analyser's name and id
     *
     * @param string $analyser
     * @param int $id
     * @return string
     */
    public static function createAnalysisHash($analyser, $id) {
        return md5(sprintf('%s.%s', $analyser, $id));
    }
}
