<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\UniqueConstraint(columns: ['agora_id', 'adherent_id'])]
#[UniqueEntity(fields: ['agora', 'adherent'], message: 'Cet adhérent est déjà membre de cette agora.')]
class AgoraMembership
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    #[Assert\NotBlank]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Agora::class, inversedBy: 'memberships')]
    public ?Agora $agora = null;

    #[Assert\NotBlank]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class, inversedBy: 'agoraMemberships')]
    public ?Adherent $adherent = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function __toString(): string
    {
        return \sprintf('[%s] %s', $this->agora?->getName(), $this->adherent?->getFullName());
    }
}
