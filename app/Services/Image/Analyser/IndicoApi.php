<?php

namespace App\Services\Image\Analyser;

use IndicoIo\IndicoIo;
use App\Contracts\Image\ImageAnalyserInterface as ImageAnalyserContract;

/**
 *
 * @author marcelo moises
 */
class IndicoApi implements ImageAnalyserContract {

    protected $config;

    /**
     *
     * @throws InvalidArgumentException
     */
    public function __construct() {
        $this->config = config('app.image.analyser.indico');

        if (is_null($this->config)) {
            throw new InvalidArgumentException("Indico API configuration is not defined.");
        }

        IndicoIo::$config['api_key'] = $this->config['api_key'];
    }

    /**
     *
     * @param array $params
     * @return boolean
     */
    public function analyse(array $params) {
        
        $response = IndicoIo::analyze_image($params['url'], $this->config['params']);

        if (!isset($response['image_recognition'])) {
            return [];
        }

        return $response;
    }

}
