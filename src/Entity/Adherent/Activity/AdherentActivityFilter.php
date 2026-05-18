<?php

declare(strict_types=1);

namespace App\Entity\Adherent\Activity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Adherent\Activity\Api\AdherentActivityFilterProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/adherent-activity-filters',
            normalizationContext: ['groups' => ['adherent_activity_filter:read']],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'contacts')",
            provider: AdherentActivityFilterProvider::class,
        ),
    ],
    routePrefix: '/v3',
)]
final readonly class AdherentActivityFilter
{
    public function __construct(
        #[Groups(['adherent_activity_filter:read'])]
        public array $eventTypes,
    ) {
    }
}
