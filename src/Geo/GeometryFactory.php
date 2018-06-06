<?php

namespace AppBundle\Geo;

use CrEOF\Spatial\PHP\Types\Geometry\GeometryInterface;
use CrEOF\Spatial\PHP\Types\Geometry\MultiPolygon;
use CrEOF\Spatial\PHP\Types\Geometry\Polygon;

class GeometryFactory
{
    public function createGeometryFromGeoJson(array $geometry): GeometryInterface
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

    public function mergeGeoJsonGeometries(array $geometries): GeometryInterface
    {
        $geometry = $this->createGeometryFromGeoJson(array_shift($geometries)['geometry']);
        $polygons = GeometryInterface::MULTIPOLYGON === $geometry->getType() ? $geometry->getPolygons() : [$geometry];
        $multiPolygon = new MultiPolygon($polygons);

        foreach ($geometries as $json) {
            $geometry = $this->createGeometryFromGeoJson($json['geometry']);
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
}
