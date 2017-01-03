<?php

namespace App\Contracts\Image;

/**
 *
 * @author marcelo moises
 */
interface ImageRepositoryInterface {

    /**
     * Performs a query within the repository
     *
     * @param array $params
     */
    function query(array $params);
}
