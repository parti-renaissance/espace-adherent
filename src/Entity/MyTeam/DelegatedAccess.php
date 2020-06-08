<?php

namespace App\Entity\MyTeam;

use App\Entity\Adherent;
use App\Entity\Committee;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MyTeam\DelegatedAccessRepository")
 * @ORM\Table(name="my_team_delegated_access")
 * @UniqueEntity(fields={"delegator", "delegated"}, message="Vous avez déjà délégué des accès à cet adhérent.")
 */
class DelegatedAccess
{
    public const DEFAULT_ROLES = [
        'Collaborateur parlementaire',
    ];

    public const ACCESS_MESSAGES = 'messages';
    public const ACCESS_EVENTS = 'events';
    public const ACCESS_ADHERENTS = 'adherents';
    public const ACCESS_COMMITTEE = 'committee';
    public const ACCESSES = [
        self::ACCESS_MESSAGES,
        self::ACCESS_EVENTS,
        self::ACCESS_ADHERENTS,
        self::ACCESS_COMMITTEE,
    ];

    /**
     * The unique auto incremented primary key.
     *
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\Length(max=50)
     */
    private $role;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     */
    private $delegator;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent", inversedBy="delegatedAccesses")
     *
     * @Assert\NotBlank(message="Veuillez sélectionner un adhérent.")
     */
    private $delegated;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array")
     *
     * @Assert\Count(min=1)
     */
    private $accesses;

    /**
     * @var Committee[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Committee")
     * @ORM\JoinTable(name="my_team_delegate_access_committee")
     */
    private $restrictedCommittees;

    /**
     * @var array|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $restrictedCities = [];

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string")
     */
    private $type;

    public function __construct()
    {
        $this->restrictedCommittees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function getDelegator(): ?Adherent
    {
        return $this->delegator;
    }

    public function setDelegator(Adherent $delegator): void
    {
        $this->delegator = $delegator;
    }

    public function getDelegated(): ?Adherent
    {
        return $this->delegated;
    }

    public function setDelegated(Adherent $delegated): void
    {
        $this->delegated = $delegated;
        $delegated->addDelegatedAccess($this);
    }

    public function getAccesses(): ?array
    {
        return $this->accesses;
    }

    public function setAccesses(array $accesses): void
    {
        $this->accesses = $accesses;
    }

    public function getRestrictedCommittees()
    {
        return $this->restrictedCommittees;
    }

    public function setRestrictedCommittees(iterable $restrictedCommittees): void
    {
        $this->restrictedCommittees = $restrictedCommittees;
    }

    public function addRestrictedCommittee(Committee $restrictedCommittee): void
    {
        if (!$this->restrictedCommittees->contains($restrictedCommittee)) {
            $this->restrictedCommittees->add($restrictedCommittee);
        }
    }

    public function getRestrictedCities(): ?array
    {
        return $this->restrictedCities;
    }

    public function setRestrictedCities(?array $restrictedCities): void
    {
        $this->restrictedCities = $restrictedCities;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
