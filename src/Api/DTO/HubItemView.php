<?php

declare(strict_types=1);

namespace App\Api\DTO;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Api\Provider\HubItemProvider;
use App\Entity\Action\Action;
use App\Entity\Event\Event;
use App\Normalizer\ImageExposeNormalizer;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/v3/hub-item',
            paginationItemsPerPage: 100,
            paginationMaximumItemsPerPage: 300,
            normalizationContext: ['groups' => ['event_list_read', 'action_read_list', ImageExposeNormalizer::NORMALIZATION_GROUP]],
            provider: HubItemProvider::class,
        ),
    ],
)]
final class HubItemView
{
    public function __construct(
        public string $type,
        public Event|Action $payload,
    ) {
    }
}
