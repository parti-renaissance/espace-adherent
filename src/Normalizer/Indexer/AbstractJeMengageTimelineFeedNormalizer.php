<?php

namespace App\Normalizer\Indexer;

abstract class AbstractJeMengageTimelineFeedNormalizer extends AbstractIndexerNormalizer
{
    final public function normalize($object, $format = null, array $context = [])
    {
        return [
            'id' => $object->getId(),
            'type' => $this->getTitle($object),
        ];
    }

    abstract protected function getTitle(object $object): string;
}
