<?php

namespace App\Normalizer\Indexer;

abstract class AbstractJeMengageTimelineFeedNormalizer extends AbstractIndexerNormalizer
{
    final public function normalize($object, $format = null, array $context = [])
    {
        return [
            'type' => $this->getType(),
            'is_local' => $this->isLocal($object),
            'uuid' => $object->getUuid()->toString(),
            'title' => $this->getTitle($object),
            'description' => $this->getDescription($object),
            'image' => $this->getImage($object),
            'date' => $this->formatDate($this->getDate($object)),
            'timeZone' => $this->getTimeZone($object),
            'author' => $this->getAuthor($object),
            'deepLink' => $this->getDeepLink($object),
        ];
    }

    abstract protected function getTitle(object $object): string;

    abstract protected function getType(): string;

    abstract protected function getDescription(object $object): ?string;

    abstract protected function isLocal(object $object): bool;

    abstract protected function getImage(object $object): ?string;

    abstract protected function getDate(object $object): ?\DateTime;

    abstract protected function getTimeZone(object $object): ?string;

    abstract protected function getAuthor(object $object): ?string;

    abstract protected function getDeepLink(object $object): ?string;
}
