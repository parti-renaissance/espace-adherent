<?php

declare(strict_types=1);

namespace App\Entity;

use App\Mailchimp\Contact\SmsOptOutSourceEnum;
use App\Repository\SmsOptOutRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SmsOptOutRepository::class)]
#[ORM\Index(fields: ['phone'])]
class SmsOptOut
{
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[ORM\Column(length: 35)]
    private readonly string $phone;

    #[ORM\Column(enumType: SmsOptOutSourceEnum::class)]
    private readonly SmsOptOutSourceEnum $source;

    #[ORM\Column]
    private readonly \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $cancelledAt = null;

    public function __construct(string $phone, SmsOptOutSourceEnum $source)
    {
        $this->phone = $phone;
        $this->source = $source;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getSource(): SmsOptOutSourceEnum
    {
        return $this->source;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCancelledAt(): ?\DateTimeImmutable
    {
        return $this->cancelledAt;
    }

    public function cancel(): void
    {
        $this->cancelledAt = new \DateTimeImmutable();
    }

    public function isCancelled(): bool
    {
        return null !== $this->cancelledAt;
    }
}
