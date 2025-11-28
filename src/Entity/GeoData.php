<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use LongitudeOne\Spatial\PHP\Types\Geometry\GeometryInterface;
use LongitudeOne\Spatial\PHP\Types\Geometry\MultiPolygon;
use LongitudeOne\Spatial\PHP\Types\Geometry\Polygon;

#[ORM\Entity]
#[ORM\Index(columns: ['geo_shape'], name: 'geo_data_geo_shape_idx', flags: ['spatial'])]
#[ORM\Table(name: 'geo_data')]
class GeoData
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var GeometryInterface
     */
    #[ORM\Column(type: 'geometry')]
    private $geoShape;

    /**
     * @throws \InvalidArgumentException if $geoShape is invalid
     */
    public function __construct(GeometryInterface $geoShape)
    {
        if (!$geoShape instanceof Polygon && !$geoShape instanceof MultiPolygon) {
            throw new \InvalidArgumentException(\sprintf('$geoShape must be an instance of %s or %s, %s given', Polygon::class, MultiPolygon::class, $geoShape::class));
        }

        $this->geoShape = $geoShape;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getGeoShape(): GeometryInterface
    {
        return $this->geoShape;
    }

    public function setGeoShape(GeometryInterface $geoShape): void
    {
        $this->geoShape = $geoShape;
    }
}
