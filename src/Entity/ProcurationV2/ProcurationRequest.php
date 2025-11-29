<?php

declare(strict_types=1);

namespace App\Entity\ProcurationV2;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\EntityUTMTrait;
use App\Procuration\V2\InitialRequestTypeEnum;
use App\Repository\Procuration\ProcurationRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProcurationRequestRepository::class)]
#[ORM\Table(name: 'procuration_v2_initial_requests')]
class ProcurationRequest
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityUTMTrait;

    #[Assert\NotBlank]
    #[ORM\Column]
    public ?string $email = null;

    #[Assert\NotBlank]
    #[ORM\Column(enumType: InitialRequestTypeEnum::class)]
    public ?InitialRequestTypeEnum $type = null;

    #[ORM\Column(length: 50, nullable: true)]
    public ?string $clientIp = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTimeInterface $remindedAt = null;

    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $adherent = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public static function createForEmail(string $email): self
    {
        $object = new self();

        $object->email = $email;

        return $object;
    }

    public function remind(): void
    {
        $this->remindedAt = new \DateTimeImmutable();
    }

    public function isReminded(): bool
    {
        return null !== $this->remindedAt;
    }

    public function isForRequest(): bool
    {
        return InitialRequestTypeEnum::REQUEST === $this->type;
    }

    public function isForProxy(): bool
    {
        return InitialRequestTypeEnum::PROXY === $this->type;
    }

    public function __toString(): string
    {
        return (string) $this->email;
    }
}
