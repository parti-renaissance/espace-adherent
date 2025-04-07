<?php

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

    #[Assert\NotBlank]
    #[ORM\Column(type: 'float')]
    public ?float $amount = null;

    #[ORM\Column(type: 'uuid')]
    public UuidInterface $token;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $tokenUsedAt = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $allowEmailNotifications = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $allowMobileNotifications = false;

    #[ORM\Column(type: 'uuid', nullable: true)]
    public ?UuidInterface $adherentUuid = null;

    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $adherent = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $cleaned = false;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->token = Uuid::uuid4();
    }

    public static function createForEmail(string $email): self
    {
        $object = new self();

        $object->email = $email;
        $object->amount = 0;

        return $object;
    }

    public function activate(): void
    {
        $this->tokenUsedAt = new \DateTime();
    }

    public function clean(): void
    {
        $this->email = '';
        $this->cleaned = true;
    }
}
