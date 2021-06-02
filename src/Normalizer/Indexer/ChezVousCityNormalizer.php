<?php

namespace App\Normalizer\Indexer;

use App\Entity\ChezVous\City;
use App\Entity\ChezVous\Marker;
use App\Entity\ChezVous\Measure;

class ChezVousCityNormalizer extends AbstractIndexerNormalizer
{
    /** @param City $object */
    public function normalize($object, $format = null, array $context = [])
    {
        if ($department = $object->getDepartment()) {
            if ($region = $department->getRegion()) {
                $region = [
                    'name' => $region->getName(),
                    'code' => $region->getCode(),
                ];
            }

            $department = [
                'name' => $department->getName(),
                'label' => $department->getLabel(),
                'code' => $department->getCode(),
                'region' => $region,
            ];
        }

        return [
            'name' => $object->getName(),
            'postalCodes' => $object->getPostalCodes(),
            'inseeCode' => $object->getInseeCode(),
            'slug' => $object->getSlug(),
            'department' => $department,
            'measures' => $object->getMeasures()->map(function (Measure $measure) {
                if ($type = $measure->getType()) {
                    $type = [
                        'code' => $type->getCode(),
                        'label' => $type->getLabel(),
                        'sourceLink' => $type->getSourceLink(),
                        'sourceLabel' => $type->getSourceLabel(),
                        'oldolfLink' => $type->getOldolfLink(),
                        'eligibilityLink' => $type->getEligibilityLink(),
                        'citizenProjectsLink' => null,
                        'ideasWorkshopLink' => null,
                        'updatedAt' => $this->formatDate($type->getUpdatedAt(), 'Y/m/d'),
                    ];
                }

                return [
                    'payload' => $measure->getPayload(),
                    'type' => $type,
                ];
            })->toArray(),
            'markers' => $object->getMarkers()->map(function (Marker $marker) {
                return [
                    'type' => $marker->getType(),
                    'coordinates' => $marker->getCoordinates(),
                ];
            })->toArray(),
            '_geoloc' => $object->getCoordinates(),
        ];
    }

    protected function getClassName(): string
    {
        return City::class;
    }
}
