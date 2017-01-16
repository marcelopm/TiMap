<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Tables\Analysers;
use App\Models\Config\Analysers as AnalysersConfig;
use Illuminate\Support\Facades\Cache;

/**
 *
 * @author marcelo moises
 */
class ImageServiceProvider extends ServiceProvider {

    public function boot() {

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {
        $this->app->bind(
                'App\Contracts\Image\ImageRepositoryInterface', 'App\Services\Image\Repository\FlickrApi'
        );

        $this->app->bind('App\Contracts\Image\ImageAnalyserInterface', function() {

            /* get heaviest analyser's name from cache or retrieve and store it forever into cache
             * until gets updated @see \App\Http\Controllers\Map\AnalyserController::weight() */
            $name = Cache::rememberForever('image.analyser.heaviest', function () {

                // retrieve from DB
                $analyser = Analysers::getHeaviest();

                if ($analyser && !empty($analyser->name)) {
                    return $analyser->name;
                } else {
                    // otherwise get default according to config and store forever
                    return Cache::rememberForever('image.analyser.default', function () {
                        return AnalysersConfig::getDefaultName();
                    });
                }
            });

            $className = sprintf('App\Services\Image\Analyser\%sApi', ucfirst($name));
            return new $className();
        });
    }

}
