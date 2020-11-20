<?php

namespace App\Normalizer\Indexer;

use App\Entity\Timeline\Measure;
use App\Entity\Timeline\Profile;

class TimelineMeasureNormalizer extends AbstractIndexerNormalizer
{
    /** @param Measure $object */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'id' => $object->getId(),
            'titles' => $object->getTitles(),
            'link' => $object->getLink(),
            'status' => $object->getStatus(),
            'major' => $object->isMajor(),
            'profileIds' => $object->getProfiles()->map(function (Profile $profile) {
                return $profile->getId();
            })->toArray(),
            'manifestoId' => $object->getManifesto() ? $object->getManifesto()->getId() : null,
            'formattedUpdatedAt' => $this->formatDate($object->getUpdatedAt(), 'Y-m-d H:i:s'),
        ];
    }

    protected function getClassName(): string
    {
        return Measure::class;
    }
}
