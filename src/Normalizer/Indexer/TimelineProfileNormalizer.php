<?php

namespace App\Normalizer\Indexer;

use App\Entity\Timeline\Profile;

class TimelineProfileNormalizer extends AbstractIndexerNormalizer
{
    /** @param Profile $object */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'id' => $object->getId(),
            'titles' => $object->getTitles(),
            'slugs' => $object->getSlugs(),
            'descriptions' => $object->getDescriptions(),
        ];
    }

    protected function getClassName(): string
    {
        return Profile::class;
    }
}
