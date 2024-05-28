<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'referent_team_member')]
#[ORM\Entity]
class ReferentTeamMember
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private $id;

    /**
     * @var Adherent
     */
    #[ORM\JoinColumn(unique: true, nullable: false, onDelete: 'CASCADE')]
    #[ORM\OneToOne(inversedBy: 'referentTeamMember', targetEntity: Adherent::class)]
    private $member;

    /**
     * @var Adherent
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private $referent;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $limited;

    /**
     * @var Committee[]|Collection
     */
    #[ORM\ManyToMany(targetEntity: Committee::class)]
    private $restrictedCommittees;

    /**
     * @var array|null
     */
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private $restrictedCities;

    public function __construct(
        Adherent $referent,
        bool $limited = false,
        array $restrictedCommittees = [],
        array $restrictedCities = []
    ) {
        $this->referent = $referent;
        $this->limited = $limited;
        $this->restrictedCommittees = new ArrayCollection($restrictedCommittees);
        $this->restrictedCities = $restrictedCities;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMember(): ?Adherent
    {
        return $this->member;
    }

    public function setMember(Adherent $member): void
    {
        $this->member = $member;
    }

    public function getReferent(): Adherent
    {
        return $this->referent;
    }

    public function isLimited(): bool
    {
        return $this->limited;
    }

    public function setLimited(bool $limited): void
    {
        $this->limited = $limited;
    }

    public function getRestrictedCommittees(): Collection
    {
        return $this->restrictedCommittees;
    }

    public function setRestrictedCommittees(array $restrictedCommittees): void
    {
        $this->restrictedCommittees = new ArrayCollection($restrictedCommittees);
    }

    public function getRestrictedCities(): ?array
    {
        return $this->restrictedCities;
    }

    public function setRestrictedCities(?array $restrictedCities): void
    {
        $this->restrictedCities = $restrictedCities;
    }

    public function getRestrictedCommitteeUuids(): array
    {
        return array_map(
            function (Committee $committee) {
                return $committee->getUuidAsString();
            },
            $this->restrictedCommittees->toArray()
        );
    }
}
