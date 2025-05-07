<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\HttpOperation;
use App\Controller\Api\Agora\JoinAgoraController;
use App\Controller\Api\Agora\LeaveAgoraController;
use App\Repository\AgoraRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(filterClass: SearchFilter::class, properties: ['name' => 'partial'])]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/v3/agoras',
            security: "is_granted('ROLE_USER')",
        ),
        new HttpOperation(
            method: 'POST',
            uriTemplate: '/v3/agoras/{uuid}/join',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: JoinAgoraController::class,
            security: 'is_granted("RENAISSANCE_ADHERENT")',
            deserialize: false,
        ),
        new HttpOperation(
            method: 'DELETE',
            uriTemplate: '/v3/agoras/{uuid}/leave',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: LeaveAgoraController::class,
            security: "is_granted('ROLE_USER')",
            deserialize: false,
        ),
    ],
    normalizationContext: ['groups' => ['agora_read', 'image_owner_exposed']],
    order: ['createdAt' => 'DESC'],
)]
#[ORM\Entity(repositoryClass: AgoraRepository::class)]
class Agora
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityNameSlugTrait;
    use EntityAdministratorBlameableTrait;

    #[Groups(['agora_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $description = null;

    #[Assert\Positive]
    #[Groups(['agora_read'])]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 50])]
    public int $maxMembersCount = 50;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    public bool $published = true;

    #[Assert\NotBlank]
    #[Groups(['agora_read'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class, inversedBy: 'presidentOfAgoras')]
    public ?Adherent $president = null;

    #[Groups(['agora_read'])]
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

    #[Groups(['agora_read'])]
    public function getMembersCount(): int
    {
        return $this->memberships->count();
    }

    public function isMembersFull(): bool
    {
        return $this->getMembersCount() >= $this->maxMembersCount;
    }
}
