<?php

namespace App\Http\Controllers\Map;

use App\Http\Controllers\Controller;

/**
 *
 * @author marcelo moises
 */
class MapController extends Controller {

    /**
     * Server the map page
     *
     * @return string
     */
    public function index() {
        return view('map');
    }

}
