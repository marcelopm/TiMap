<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Analyser;
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
            $classNameTpl = 'App\Services\Image\Analyser\%sApi';

            /* get heaviest analyser's name from cache or retrieve and store it forever into cache
             * until gets updated @see \App\Http\Controllers\Map\AnalyserController::weight() */
            $name = Cache::rememberForever('image.analyser.heaviest', function () use ($classNameTpl) {

                // retrieve from DB
                $analyser = Analyser::getHeaviest();
                
                $className = sprintf($classNameTpl, ucfirst(data_get($analyser, 'name')));
                if (class_exists($className)) {
                    return $analyser->name;
                } else {
                    // otherwise get default according to config and store forever
                    return Cache::rememberForever('image.analyser.default', function () {
                        return AnalysersConfig::getDefaultName();
                    });
                }
            });

            $className = sprintf($classNameTpl, ucfirst($name));
            return new $className();
        });
    }

}
