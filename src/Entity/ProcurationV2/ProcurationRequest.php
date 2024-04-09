<?php

namespace App\Entity\ProcurationV2;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\EntityUTMTrait;
use App\Procuration\V2\InitialRequestTypeEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="procuration_v2_initial_requests")
 * @ORM\Entity(repositoryClass="App\Repository\Procuration\ProcurationRequestRepository")
 */
class ProcurationRequest
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityUTMTrait;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     */
    public ?string $email = null;

    /**
     * @ORM\Column(enumType=InitialRequestTypeEnum::class)
     *
     * @Assert\NotBlank
     */
    public ?InitialRequestTypeEnum $type = null;

    /**
     * @ORM\Column(length=50, nullable=true)
     */
    public ?string $clientIp = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    public ?\DateTimeInterface $remindedAt = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public static function createForEmail(string $email, InitialRequestTypeEnum $type): self
    {
        $object = new self();

        $object->email = $email;
        $object->type = $type;

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
}
