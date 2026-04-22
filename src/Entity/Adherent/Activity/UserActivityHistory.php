<?php

declare(strict_types=1);

namespace App\Entity\Adherent\Activity;

use App\Adherent\Activity\SourceTypeEnum;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Repository\Adherent\Activity\UserActivityHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\Column(length: 50, enumType: SourceTypeEnum::class)]
    public SourceTypeEnum $sourceType;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    public int $sourceId = 0;

    #[ORM\Column(length: 100)]
    public string $eventType = '';

    #[ORM\Column(type: 'datetime')]
    public ?\DateTimeInterface $occurredAt = null;

    #[ORM\Column(type: 'json', nullable: true)]
    public ?array $metadata = null;

    #[ORM\Column(type: 'datetime')]
    public ?\DateTimeInterface $createdAt = null;
}
