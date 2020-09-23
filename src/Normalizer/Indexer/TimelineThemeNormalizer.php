<?php

namespace App\Normalizer\Indexer;

use App\Entity\Timeline\Measure;
use App\Entity\Timeline\Theme;

class TimelineThemeNormalizer extends AbstractIndexerNormalizer
{
    /** @param Theme $object */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'id' => $object->getId(),
            'titles' => $object->getTitles(),
            'slugs' => $object->getSlugs(),
            'descriptions' => $object->getDescriptions(),
            'featured' => $object->isFeatured(),
            'image' => $object->getImage(),
            'measureIds' => $object->getMeasures()->map(function (Measure $measure) {
                return $measure->getId();
            })->toArray(),
            'measureTitles' => $object->getMeasureTitles(),
            'profileIds' => $object->getProfileIds(),
            'manifestoIds' => $object->getManifestoIds(),
        ];
    }

    protected function getClassName(): string
    {
        return Theme::class;
    }
}
