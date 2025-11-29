<?php

declare(strict_types=1);

namespace App\Utils;

use LongitudeOne\Spatial\PHP\Types\Geometry\MultiPolygon;
use LongitudeOne\Spatial\PHP\Types\Geometry\Polygon;

abstract class GeometryUtils
{
    public static function mergeWkt(array $wktCollection): MultiPolygon
    {
        $polygons = [];

        foreach ($wktCollection as $wkt) {
            $wkt = trim($wkt);

            if (!str_contains($wkt, 'POLYGON')) {
                continue;
            }

            $wkt = trim(str_replace(['MULTIPOLYGON', 'POLYGON'], '', $wkt));

            if (!preg_match('/\([^)]+\)/', $wkt, $matches)) {
                continue;
            }

            foreach ($matches as $match) {
                $coordinatePairs = explode(',', trim(trim($match, '()')));
                $coordinates = [];
                foreach ($coordinatePairs as $pair) {
                    $points = preg_split('/\s+/', trim($pair));
                    if (2 !== \count($points)) {
                        throw new \InvalidArgumentException("Invalid coordinate pair: $pair in MULTIPOLYGON WKT: $wkt");
                    }

                    $coordinates[] = [
                        round((float) $points[0], 5),
                        round((float) $points[1], 5),
                    ];
                }

                if ($coordinates[0] !== end($coordinates)) {
                    $coordinates[] = $coordinates[0];
                }

                $polygons[] = new Polygon([$coordinates]);
            }
        }

        return new MultiPolygon($polygons);
    }
}
