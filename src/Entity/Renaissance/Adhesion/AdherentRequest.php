<?php

declare(strict_types=1);

namespace App\Entity\Renaissance\Adhesion;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\EntityUTMTrait;
use App\Repository\Renaissance\Adhesion\AdherentRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdherentRequestRepository::class)]
class AdherentRequest
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityUTMTrait;

    #[Assert\NotBlank]
    #[ORM\Column(nullable: true)]
    public ?string $email = null;

    #[ORM\Column(type: 'uuid', nullable: true)]
    public ?UuidInterface $emailHash = null;

    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $adherent = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTimeInterface $accountCreatedAt = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public static function createForEmail(string $email): self
    {
        $object = new self();

        $object->email = $email;
        $object->emailHash = Adherent::createUuid($email);

        return $object;
    }

    public function handleAccountCreated(Adherent $adherent): void
    {
        $this->email = null;
        $this->adherent = $adherent;
        $this->accountCreatedAt = new \DateTime();
    }
}
