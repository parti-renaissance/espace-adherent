<?php

declare(strict_types=1);

namespace App\Entity\Adherent\Activity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use App\Adherent\Activity\SourceTypeEnum;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Repository\Adherent\Activity\AdherentActivityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiFilter(filterClass: SearchFilter::class,
    properties: [
        'sourceType' => 'exact',
        'eventType' => 'exact',
    ]
)]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/adherents/{uuid}/activity',
            uriVariables: [
                'uuid' => new Link(
                    toProperty: 'adherent',
                    fromClass: Adherent::class,
                    security: "is_granted('MANAGE_ZONEABLE_ITEM__FOR_SCOPE', adherent)",
                ),
            ],
            requirements: ['uuid' => '%pattern_uuid%'],
            paginationItemsPerPage: 10,
            order: ['occurredAt' => 'DESC'],
            normalizationContext: ['groups' => ['adherent_activity:list']],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'contacts')",
        ),
    ],
    routePrefix: '/v3',
)]
#[ORM\Entity(repositoryClass: AdherentActivityRepository::class)]
#[ORM\Index(columns: ['adherent_id', 'occurred_at'])]
#[ORM\UniqueConstraint(columns: ['source_type', 'source_id'])]
class AdherentActivity
{
    use EntityIdentityTrait;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $adherent = null;

    #[Groups(['adherent_activity:list'])]
    #[ORM\Column(length: 50, enumType: SourceTypeEnum::class)]
    public SourceTypeEnum $sourceType;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    public int $sourceId = 0;

    #[Groups(['adherent_activity:list'])]
    #[ORM\Column(length: 100)]
    public string $eventType = '';

    #[Groups(['adherent_activity:list'])]
    #[ORM\Column(type: 'datetime')]
    public ?\DateTimeInterface $occurredAt = null;

    #[ORM\Column(type: 'json', nullable: true)]
    public ?array $metadata = null;

    #[ORM\Column(type: 'datetime')]
    public ?\DateTimeInterface $createdAt = null;
}
