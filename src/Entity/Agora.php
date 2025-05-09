<?php

namespace App\Entity;

use App\Repository\AgoraRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AgoraRepository::class)]
class Agora
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityNameSlugTrait;
    use EntityAdministratorBlameableTrait;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $description = null;

    #[Assert\Positive]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 50])]
    public int $maxMembersCount = 50;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    public bool $published = true;

    #[Assert\NotBlank]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class, inversedBy: 'presidentOfAgoras')]
    public ?Adherent $president = null;

    #[ORM\JoinTable(name: 'agora_general_secretaries')]
    #[ORM\ManyToMany(targetEntity: Adherent::class, inversedBy: 'generalSecretaryOfAgoras')]
    public Collection $generalSecretaries;

    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'agora', targetEntity: AgoraMembership::class, cascade: ['all'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    public Collection $memberships;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->generalSecretaries = new ArrayCollection();
        $this->memberships = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function addMembership(AgoraMembership $membership): void
    {
        if (!$this->memberships->contains($membership)) {
            $membership->agora = $this;

            $this->memberships->add($membership);
        }
    }

    public function removeMembership(AgoraMembership $membership): void
    {
        $this->memberships->removeElement($membership);
    }
}
