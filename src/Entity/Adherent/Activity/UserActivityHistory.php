<?php

declare(strict_types=1);

namespace App\Entity\Adherent\Activity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Adherent\Activity\SourceTypeEnum;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Repository\Adherent\Activity\UserActivityHistoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiFilter(filterClass: SearchFilter::class,
    properties: [
        'uuid' => 'exact',
        'adherent.uuid' => 'exact',
        'sourceType' => 'exact',
        'eventType' => 'exact']
)]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/v3/user_activity_histories',
            paginationItemsPerPage: 30,
            order: ['occurredAt' => 'DESC'],
            normalizationContext: ['groups' => ['user_activity_history:list']],
            security: "is_granted('ROLE_USER')",
        ),
    ],
)]
#[ORM\Entity(repositoryClass: UserActivityHistoryRepository::class)]
#[ORM\Index(columns: ['adherent_id', 'occurred_at'], name: 'idx_app_user_activity_history_adherent_date')]
#[ORM\Table(name: 'app_user_activity_history')]
#[ORM\UniqueConstraint(name: 'uniq_app_user_activity_history_source', columns: ['source_type', 'source_id'])]
class UserActivityHistory
{
    use EntityIdentityTrait;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $adherent = null;

    #[Groups(['user_activity_history:list'])]
    #[ORM\Column(length: 50, enumType: SourceTypeEnum::class)]
    public SourceTypeEnum $sourceType;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    public int $sourceId = 0;

    #[Groups(['user_activity_history:list'])]
    #[ORM\Column(length: 100)]
    public string $eventType = '';

    #[Groups(['user_activity_history:list'])]
    #[ORM\Column(type: 'datetime')]
    public ?\DateTimeInterface $occurredAt = null;

    #[Groups(['user_activity_history:list'])]
    #[ORM\Column(type: 'json', nullable: true)]
    public ?array $metadata = null;

    #[ORM\Column(type: 'datetime')]
    public ?\DateTimeInterface $createdAt = null;
}
