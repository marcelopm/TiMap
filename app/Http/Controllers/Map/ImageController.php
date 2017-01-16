<?php

namespace App\Http\Controllers\Map;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Contracts\Image\ImageRepositoryInterface;
use App\Contracts\Image\ImageAnalyserInterface;
use App\Helpers\Image as ImageHelper;

/**
 *
 * @author marcelo moises
 */
class ImageController extends Controller {

    /**
     * The image repository and analyser gets inject by the IoC, @see \App\Providers\ImageServiceProvider
     *
     * @param ImageRepositoryInterface $repository
     * @param ImageAnalyserInterface $analyser
     */
    public function __construct(ImageRepositoryInterface $repository, ImageAnalyserInterface $analyser) {
        $this->repository = $repository;
        $this->analyser = $analyser;
    }
    
    /**
     * Handles a XHR made in order to perform a image analysis - tagging, face recognition, etc
     *
     * @param Request $request
     * @return string - JSON
     */
    public function analyse(Request $request) {

        // image id and url
        $this->validate($request, [
            'id' => 'required|integer',
            'url' => 'required|string'
        ]);

        $params = $request->only('id', 'url');

        // get image from cache or an empty array with the analysis
        $image = Cache::get($params['id'], [
                    'analysis' => []
        ]);

        // in case the image has no analysis, proceed to get it done
        if (empty($image['analysis'])) {
            // request an analysis from the current anaylser and add to the image
            $image = array_replace_recursive($image, [
                'analysis' => $this->analyser->analyse(array_only($params, ['url']))
            ]);

            // get current anaylser's name from cache
            $analyser = Cache::get('image.analyser.heaviest');

            /* relate analysis to the image and a hashkey to avoid flawness analyser weight operations
             * @see AnalyserController::weight()
             */
            $image['analysis'] = array_replace_recursive($image['analysis'], [
                'hashes' => [
                    'analyser' => ImageHelper::createAnalysisHash($analyser, $params['id'])
                ]
            ]);

            // in case the image is cached, increase its timelife - 30 days
            if (!empty($image['id'])) {
                Cache::put($image['id'], $image, 60 * 24 * 30);
            }
        }

        return response()->json($image['analysis']);
    }

    /**
     * Handles a XHR made in order to search for images given a map's bounding box coordinates
     *
     * @param Request $request
     * @return string - JSON
     */
    public function search(Request $request) {

        // map's min and max latitude and longtude
        $this->validate($request, [
            'minlng' => 'required|numeric',
            'minlat' => 'required|numeric',
            'maxlng' => 'required|numeric',
            'maxlat' => 'required|numeric',
        ]);

        // map's bounding box
        $boundingBox = $request->only('minlng', 'minlat', 'maxlng', 'maxlat');

        // cap down the coorinates digits for response caching purpose
        // othewise XHR get's fired by any slightly changes on the browser's viewport
        $roundedBoundingBox = array_map(function($value) {
            $isNegative = abs($value) != $value ? true : false;
            return substr($value, 0, $isNegative ? 9 : 8);
        }, $boundingBox);

        // create a cache key based on the given coorinates from the current map's viewport
        $cacheKey = sha1(implode(':', $roundedBoundingBox));

        // get the response from cache, otherwise create an empty array
        $response = Cache::get($cacheKey, [
                    'images' => []
        ]);

        // in case there are cached images
        if (!empty($response['images'])) {
            /*
             * check for updated photos - containing already analysis and maybe review, @see ImageController::analyse()
             */
            $hasUpdate = false;
            foreach ($response['images'] as $key => $image) {
                if ($cacheImage = Cache::get($image['id'])) {
                    $hasUpdate = true;
                    $response['images'][$key] = $cacheImage;
                }
            }

            // update cache with the updated images
            if ($hasUpdate) {
                // cache the response - 5 minutes
                Cache::put($cacheKey, $response, 5);
            }
        } else {
            // make search request to the repository for the given map's bounding box coodinates
            $images = $this->repository->query(array(
                'bbox' => sprintf('%s, %s, %s, %s', $boundingBox['minlng'], $boundingBox['minlat'], $boundingBox['maxlng'], $boundingBox['maxlat'])
            ));

            // if the response contain a list of photos
            foreach ($images as $image) {

                // try to get image from cache, possible with analysis data
                if (Cache::has($image['id'])) {
                    $image = Cache::get($image['id']);
                } else {
                    // otherwise store it into cache for - 1 day
                    Cache::put($image['id'], $image, 60 * 24);
                }

                // add the photo to the reponse's list
                $response['images'][] = $image;
            }

            // cache the response - 5 minutes
            Cache::put($cacheKey, $response, 5);
        }

        return response()->json($response);
    }

}