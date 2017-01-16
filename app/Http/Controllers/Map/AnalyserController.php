<?php

namespace App\Http\Controllers\Map;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Models\Analyser;
use App\Models\Tables\Analysers;

/**
 *
 * @author marcelo moises
 */
class AnalyserController extends Controller {

    /**
     * Handles a XHR made to increase or decrease the current image analyser's weight being used
     *
     * @param Request $request
     * @param string $operation
     * @return string - JSON
     */
    public function weight(Request $request, $operation) {

        // image id
        $this->validate($request, [
            'id' => 'required|integer',
            'hash' => 'required|string'
        ]);

        $params = $request->only('id', 'hash');

        // get the analyser based on image id and analysis hash
        $analyser = Analysers::getFromAnalysisHash($params['id'], $params['hash']);

        // get image from cache
        $image = Cache::get($params['id']);

        if (!$analyser || !$image) {
            throw new \Exception('One or more required resources are undefined');
        }

        // unique review hash containing analyser name and image id
        $reviewHash = sha1(sprintf('%s.%s', $analyser->name, $params['id']));
        // in case it's analysis hasn't being reviewed yet, them proceed to change analyser's weight
        if (!isset($image['analysis']['hashes']['review']) || $image['analysis']['hashes']['review'] !== $reviewHash) {

            // change analyser's weight
            if ($operation === 'increase') {
                $analyser->incrementWeight();
            } else {
                $analyser->decrementWeight();
            }

            /* update cache with the heaviest @see \App\Providers\ImageServiceProvider */
            Cache::forever('image.analyser.heaviest', Analysers::getHeaviest()->name);

            // tag the image as reviewed - add review hash to image
            $image['analysis'] = array_replace_recursive($image['analysis'], [
                'hashes' => [
                    'review' => $reviewHash
                ]
            ]);

            // update cached image
            Cache::put($image['id'], $image, 60 * 24 * 30);
        }

        return response()->json($reviewHash);
    }

}
