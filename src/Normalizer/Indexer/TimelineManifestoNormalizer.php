<?php

namespace App\Normalizer\Indexer;

use App\Entity\Timeline\Manifesto;

class TimelineManifestoNormalizer extends AbstractIndexerNormalizer
{
    /** @param Manifesto $object */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'id' => $object->getId(),
            'image' => $object->getImage(),
            'titles' => $object->getTitles(),
            'slugs' => $object->getSlugs(),
            'descriptions' => $object->getDescriptions(),
        ];
    }

    protected function getClassName(): string
    {
        return Manifesto::class;
    }
}
