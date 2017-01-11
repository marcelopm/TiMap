<?php

namespace App\Services\Image\Analyser;

use GuzzleHttp\Client;
use App\Contracts\Image\ImageAnalyserInterface as ImageAnalyserContract;

/**
 *
 * @author marcelo moises
 */
class ImaggaApi implements ImageAnalyserContract {

    protected $config;
    protected $http;

    /**
     *
     * @throws InvalidArgumentException
     */
    public function __construct() {
        $this->config = config('app.image.analyser.imagga');

        if (is_null($this->config)) {
            throw new InvalidArgumentException("Imagga API configuration is not defined.");
        }

        $this->http = new Client([
            'base_uri' => $this->config['api_endpoint'],
            'auth' => [
                $this->config['api_key'],
                $this->config['client_secret']
            ]
        ]);
    }

    /**
     *
     * @param array $params
     * @return boolean
     */
    public function analyse(array $params) {

        $response = $this->http->request('GET', 'tagging', ['query' =>
            array_replace_recursive(
                    $this->config['params'], $params
            )
        ]);

        $body = json_decode($response->getBody(), true);
        $data = array_get($response, 'results.0.tags', []);

        return [
            'image_recognition' => $this->formatResponse($data)
        ];
    }

    public function formatResponse($tags) {
        $data = [];

        // sum the confidence rating, so we can cap down the sum to 100%
        $total = 0;
        foreach ($tags as $tag) {
            $total += $tag['confidence'];
        }

        foreach ($tags as $tag) {
            $data[$tag['tag']] = $tag['confidence'] / $total;
        }

        return $data;
    }

}
