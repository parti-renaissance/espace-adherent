<?php

namespace App\Geo;

use CrEOF\Spatial\PHP\Types\Geometry\GeometryInterface;
use CrEOF\Spatial\PHP\Types\Geometry\MultiPolygon;
use CrEOF\Spatial\PHP\Types\Geometry\Polygon;

class GeometryFactory
{
    public static function createGeometryFromGeoJson(array $geometry): GeometryInterface
    {
        switch ($geometry['type']) {
            case GeometryInterface::POLYGON:
                return new Polygon($geometry['coordinates']);

            case GeometryInterface::MULTIPOLYGON:
                return new MultiPolygon($geometry['coordinates']);

            default:
                throw new \InvalidArgumentException("${$geometry['type']} is not supported");
        }
    }

    public static function mergeGeoJsonGeometries(array $geometries): GeometryInterface
    {
        $geometry = static::createGeometryFromGeoJson(array_shift($geometries)['geometry']);
        $polygons = GeometryInterface::MULTIPOLYGON === $geometry->getType() ? $geometry->getPolygons() : [$geometry];
        $multiPolygon = new MultiPolygon($polygons);

        foreach ($geometries as $json) {
            $geometry = static::createGeometryFromGeoJson($json['geometry']);
            if (GeometryInterface::POLYGON === $geometry->getType()) {
                $multiPolygon->addPolygon($geometry->toArray());
            } else {
                foreach ($geometry->getPolygons() as $polygon) {
                    $multiPolygon->addPolygon($polygon->toArray());
                }
            }
        }

        return $multiPolygon;
    }

    public static function createGeometry(array $polygons): GeometryInterface
    {
        if (1 === \count($polygons)) {
            return static::createGeometryFromGeoJson($polygons[0]['geometry']);
        }

        return static::mergeGeoJsonGeometries($polygons);
    }
}
