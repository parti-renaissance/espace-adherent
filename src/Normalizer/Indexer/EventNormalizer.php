<?php

namespace App\Normalizer\Indexer;

use App\Entity\Event;

class EventNormalizer extends AbstractIndexerNormalizer
{
    /** @param Event $object */
    public function normalize($object, $format = null, array $context = [])
    {
        $committee = $object->getCommittee();

        return array_merge(
            [
                'uuid' => $object->getUuidAsString(),
                'name' => $object->getName(),
                'canonicalName' => $object->getCanonicalName(),
                'slug' => $object->getSlug(),
                'description' => $object->getDescription(),
                'begin_at' => $this->formatDate($object->getBeginAt()),
                'finish_at' => $this->formatDate($object->getFinishAt()),
                'address' => $object->getInlineFormattedAddress(),
                'address_city' => $object->getCityName(),
                '_geoloc' => $object->getGeolocalisation(),
                'created_at' => $this->formatDate($object->getCreatedAt()),
                'updated_at' => $this->formatDate($object->getCreatedAt()),
                'category' => [
                    'name' => $object->getCategoryName(),
                ],
            ],
            $committee ?
            [
                'committee' => [
                    'name' => $committee->getName(),
                    'canonicalName' => $committee->getCanonicalName(),
                    'slug' => $committee->getSlug(),
                    'description' => $committee->getDescription(),
                    'membersCount' => $committee->getMembersCount(),
                    'uuid' => $committee->getUuidAsString(),
                    'address' => $committee->getInlineFormattedAddress(),
                    'address_city' => $committee->getCityName(),
                    '_geoloc' => $committee->getGeolocalisation(),
                    'created_at' => $this->formatDate($committee->getCreatedAt()),
                    'updated_at' => $this->formatDate($committee->getCreatedAt()),
                ],
            ] : []
        );
    }

    protected function getClassName(): string
    {
        return Event::class;
    }
}
