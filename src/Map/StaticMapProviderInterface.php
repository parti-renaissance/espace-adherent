<?php

namespace App\Map;

use App\Geocoder\Coordinates;

interface StaticMapProviderInterface
{
    /**
     * Generate an image for the given coordinates using a static map provider.
     *
     * @param Coordinates $coordinates the coordinates to generate a map for
     * @param $size Size of the generated map (ex: 400x400)
     *
     * @return string|false return the map image content or false if an error occured
     */
    public function get(Coordinates $coordinates, ?string $size = null);
}
