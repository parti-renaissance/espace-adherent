<?php

namespace App\Entity\TerritorialCouncil;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityReferentTagTrait;
use App\Entity\ReferentTag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     name="territorial_council",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="territorial_council_uuid_unique", columns="uuid"),
 *         @ORM\UniqueConstraint(name="territorial_council_name_unique", columns="name"),
 *         @ORM\UniqueConstraint(name="territorial_council_codes_unique", columns="codes")
 *     }
 * )
 * @ORM\Entity
 *
 * @UniqueEntity("name")
 *
 * @Algolia\Index(autoIndex=false)
 */
class TerritorialCouncil
{
    use EntityIdentityTrait;
    use EntityReferentTagTrait;

    /**
     * @ORM\Column(length=255, unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $name;

    /**
     * @ORM\Column(length=50, unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=50)
     */
    private $codes;

    /**
     * @var Collection|ReferentTag[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\ReferentTag", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="territorial_council_referent_tag",
     *     joinColumns={
     *         @ORM\JoinColumn(name="territorial_council_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="referent_tag_id", referencedColumnName="id", onDelete="CASCADE")
     *     }
     * )
     */
    protected $referentTags;

    /**
     * @var Collection|TerritorialCouncilMembership[]
     *
     * @ORM\OneToMany(
     *     targetEntity=TerritorialCouncilMembership::class,
     *     cascade={"persist", "remove"},
     *     mappedBy="territorialCouncil",
     *     orphanRemoval=true
     * )
     */
    private $memberships;

    public function __construct(string $name = null, string $codes = null)
    {
        $this->uuid = Uuid::uuid4();
        $this->name = $name;
        $this->codes = $codes;
        $this->referentTags = new ArrayCollection();
        $this->memberships = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCodes(): ?string
    {
        return $this->codes;
    }

    public function setCodes(string $codes): void
    {
        $this->codes = $codes;
    }

    /**
     * @return Collection|TerritorialCouncilMembership[]
     */
    public function getMemberships(): Collection
    {
        return $this->memberships;
    }

    public function addMembership(TerritorialCouncilMembership $memberships): void
    {
        if (!$this->memberships->contains($memberships)) {
            $this->memberships->add($memberships);
        }
    }

    public function removeMembership(TerritorialCouncilMembership $memberships): void
    {
        $this->memberships->remove($memberships);
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
