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
            provider: HubItemProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/hub-item',
            provider: HubItemProvider::class,
        ),
    ],
    normalizationContext: ['groups' => ['event_list_read', 'action_read_list', ImageExposeNormalizer::NORMALIZATION_GROUP]],
    paginationItemsPerPage: 100,
    paginationMaximumItemsPerPage: 300,
)]
class HubItemView
{
    public function __construct(
        public string $type,
        public Event|Action $payload,
    ) {
    }
}
