<?php

namespace App\Contracts\Image;

/**
 *
 * @author marcelo moises
 */
interface ImageAnalyserInterface {

    /**
     * Performs image analysis
     *
     * @param array $params
     */
    function analyse(array $params);
}
