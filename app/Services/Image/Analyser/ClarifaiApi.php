<?php

namespace App\Services\Image\Analyser;

use GuzzleHttp\Client;
use App\Contracts\Image\ImageAnalyserInterface as ImageAnalyserContract;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 *
 * @author marcelo moises
 */
class ClarifaiApi implements ImageAnalyserContract {

    protected $config;
    protected $http;
    protected $baseParams = [];

    /**
     *
     * @throws InvalidArgumentException
     */
    public function __construct() {
        $this->config = config('app.image.analyser.clarifai');

        if (is_null($this->config)) {
            throw new InvalidArgumentException("Imagga API configuration is not defined.");
        }

        // create new http client
        $this->http = new Client([
            'base_uri' => $this->config['api_endpoint'],
        ]);

        // and api access token
        $this->configureAccessToken();
    }

    /**
     * Send an authentication request and return relevant information
     *
     * @return array
     * @throws \Exception in case the necessary params are not present in the response
     */
    protected function authenticate() {
        // send a request with api info
        $response = $this->http->post('token', [
            'auth' => [
                $this->config['api_key'],
                $this->config['client_secret']
            ]
        ]);

        $body = json_decode($response->getBody(), true);
        $data = array_filter(array_only($body, ['access_token', 'expires_in']));

        if (count($data) !== 2) {
            throw new \Exception('No access token information supplied');
        }

        return $data;
    }

    /**
     * Get access token from cache or from authentication request
     *
     * @return string
     */
    protected function getAccessToken() {
        // get the access token from cache and if not present sent an auth request and store it
        $token = Cache::get('image.analyser.clarifai.access-token', function () {
            $data = $this->authenticate();

            // set the expiry time given the auth response
            $expiresAt = Carbon::now()->addMinutes($data['expires_in'] - 1);

            // store the access token for later use
            Cache::put('image.analyser.clarifai.access-token', $data['access_token'], $expiresAt);

            return $data['access_token'];
        });

        return $token;
    }

    /**
     * get access token and store into base headers
     */
    protected function configureAccessToken() {
        $this->baseParams['headers']['Authorization'] = sprintf('Bearer %s', $this->getAccessToken());
    }

    /**
     * Create a request input object given params containing the image url
     *
     * @param array $params
     * @return type
     */
    protected function createInputParam($params) {
        $json = sprintf('{
            "inputs": [{
                "data": {
                    "image": {
                        "url": "%s"
                    }
                }
            }
        ]}', $params['image']['url']);

        return json_decode($json);
    }

    /**
     * Proxy a request and authenticate in case of a 401 - unauthorized and retry
     *
     * @param string $uri
     * @param array $params
     * @param boolean $retry
     * @return type
     */
    protected function proxyRequest($uri, $params, $retry = false) {
        // make a request for a given uri and params
        $response = $this->http->request('post', $uri, array_replace_recursive([
            'json' => $this->createInputParam($params),
            'http_errors' => false
        ], $this->baseParams));

        // in case of unauthorized error and if it's a first attempt
        if ($response->getStatusCode() === 401 && !$retry) {
            // try to reconfigure the access token
            $this->configureAccessToken();
            // and retry
            $this->proxyRequest($uri, $params, true);
        }

        return json_decode($response->getBody(), true);
    }

    /**
     *
     * @param array $params
     * @return boolean
     */
    public function analyse(array $params) {

        $response = $this->proxyRequest('models/aaa03c23b3724a16a56b629203edc62c/outputs', [
            'image' => $params
        ]);

        $data = array_get($response, 'outputs.0.data.concepts', []);

        return [
            'image_recognition' => $this->formatResponse($data)
        ];
    }

    public function formatResponse($tags) {
        $data = [];

        // get the first 3 tags
        $tags = array_slice($tags, -3);

        // sum the confidence rating, so we can cap down the sum to 100%
        $total = 0;
        foreach ($tags as $tag) {
            $total += $tag['value'];
        }

        foreach ($tags as $tag) {
            $data[$tag['name']] = $tag['value'] / $total;
        }

        return $data;
    }

}
