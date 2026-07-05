<?php

declare(strict_types=1);

namespace App\Entity\Ses;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Index(fields: ['campaignUuid', 'eventType'])]
#[ORM\Index(fields: ['sesMessageId'])]
class SesEvent
{
    #[ORM\Column(type: Types::INTEGER, options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    public ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    public string $snsMessageId;

    #[ORM\Column(length: 50, nullable: true)]
    public ?string $eventType = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $sesMessageId = null;

    #[ORM\Column(length: 36, nullable: true)]
    public ?string $campaignUuid = null;

    #[ORM\Column(length: 36, nullable: true)]
    public ?string $adherentUuid = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $recipient = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public ?\DateTimeImmutable $occurredAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    public \DateTimeImmutable $receivedAt;

    #[ORM\Column(type: Types::JSON)]
    public array $payload;
}
