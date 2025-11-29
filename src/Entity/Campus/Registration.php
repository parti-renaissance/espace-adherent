<?php

declare(strict_types=1);

namespace App\Entity\Campus;

use App\Campus\RegistrationStatusEnum;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'campus_registration')]
class Registration
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[ORM\Column(length: 50)]
    public ?string $eventMakerId = null;

    #[ORM\Column(length: 50)]
    public ?string $campusEventId = null;

    #[ORM\Column(length: 50)]
    public ?string $eventMakerOrderUid = null;

    #[ORM\Column(enumType: RegistrationStatusEnum::class)]
    public ?RegistrationStatusEnum $status = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTimeInterface $registeredAt = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class, inversedBy: 'campusRegistrations')]
    public ?Adherent $adherent = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function isValid(): bool
    {
        return \in_array($this->status, [
            RegistrationStatusEnum::INVITED,
            RegistrationStatusEnum::REGISTERED,
        ], true);
    }
}
