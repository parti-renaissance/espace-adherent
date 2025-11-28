<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AgoraMembershipRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AgoraMembershipRepository::class)]
#[ORM\UniqueConstraint(columns: ['agora_id', 'adherent_id'])]
#[UniqueEntity(fields: ['adherent', 'agora'], message: 'Cet adhérent est déjà membre de cette agora.')]
class AgoraMembership implements EntityAdministratorBlameableInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    #[Assert\NotBlank]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Agora::class, inversedBy: 'memberships')]
    public ?Agora $agora = null;

    #[Assert\NotBlank]
    #[Groups(['agora_membership_read'])]
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
