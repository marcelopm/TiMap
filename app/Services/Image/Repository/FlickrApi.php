<?php

namespace App\Services\Image\Repository;

use GuzzleHttp\Client;
use App\Contracts\Image\ImageRepositoryInterface as ImageRepositoryContract;

/**
 *
 * @author marcelo moises
 */
class FlickrApi implements ImageRepositoryContract {

    protected $config;
    protected $defaultParams = [
        'method' => 'flickr.photos.search'
    ];
    protected $api;

    /**
     *
     * @throws InvalidArgumentException
     */
    public function __construct() {
        $this->config = config('app.image.provider.flickr');

        if (is_null($this->config)) {
            throw new InvalidArgumentException("Flick API configuration is not defined.");
        }

        $this->defaultParams += array_only($this->config, ['api_key', 'format']);

        $this->http = new Client([
            'base_uri' => $this->config['api_endpoint'],
        ]);
    }

    /**
     *
     * @param array $params
     * @return boolean
     */
    public function query(array $params) {
        // send a search request to flickr api with the given param
        $response = $this->http->request('GET', '?', ['query' =>
            array_replace_recursive(
                    $this->config['params'], $params, $this->defaultParams
            )
        ]);

        $contents = $response->getBody()->getContents();
        if (empty($contents)) {
            return [];
        }
        
        $response = unserialize($contents);

        // if no photos are returned, return false as a result
        if (empty($response['photos']['photo'])) {
            return [];
        }

        $images = array();
        foreach ($response['photos']['photo'] as $image) {

            if (empty($image['url_z']) && empty($image['url_o'])) {
                continue;
            }

            // creat a photo array
            $images[] = [
                'id' => $image['id'],
                'title' => $image['title'],
                'url' => [
                    'medium' => !empty($image['url_z']) ? $image['url_z'] : $image['url_o'],
                    'original' => !empty($image['url_o']) ? $image['url_o'] : $image['url_z'],
                ],
                'lat' => $image['latitude'],
                'lng' => $image['longitude'],
            ];
        }

        return $images;
    }

}
