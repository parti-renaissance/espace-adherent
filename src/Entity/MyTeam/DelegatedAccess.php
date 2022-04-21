<?php

namespace App\Entity\MyTeam;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MyTeam\DelegatedAccessRepository")
 * @ORM\Table(name="my_team_delegated_access")
 * @UniqueEntity(fields={"delegator", "delegated", "type"}, message="Vous avez déjà délégué des accès à cet adhérent.")
 */
class DelegatedAccess
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    public const ATTRIBUTE_KEY = 'delegated_access_uuid';

    public const DEFAULT_ROLES = [
        'Responsable communication',
        'Responsable mobilisation',
        'Responsable phoning',
    ];

    public const DEFAULT_REFERENT_ROLES = [
        'Responsables territoriaux' => [
            'Responsable des Comités',
            'Responsable Logistique',
            'Responsable Mobilisation',
            'Responsable Talents / Formation',
        ],
        'Responsables communications et digitaux' => [
            'Responsable Communication',
            'Responsable Réseaux Sociaux',
            'Responsable Digital',
            'Responsable Contenu',
        ],
        'Responsables thématiques' => [
            'Responsable Engagement Citoyen',
            'Responsable Europe',
            'Responsable Société Civile',
            'Responsable Idée',
        ],
        'Responsables administratifs' => [
            'Responsable Financier',
            'Secrétaire Général',
            'Responsable Élus',
            'Responsable des Elections',
            'Responsable procurations',
            'Responsable bureaux de vote / assesseurs',
        ],
        'Opération #AVosCotes' => [
            'Appelant',
        ],
    ];

    public const ACCESS_MESSAGES = 'messages';
    public const ACCESS_EVENTS = 'events';
    public const ACCESS_ADHERENTS = 'adherents';
    public const ACCESS_COMMITTEE = 'committee';
    public const ACCESS_POLLS = 'polls';
    public const ACCESS_JECOUTE = 'jecoute';
    public const ACCESS_JECOUTE_REGION = 'jecoute_region';
    public const ACCESS_JECOUTE_NEWS = 'jecoute_news';
    public const ACCESS_ELECTED_REPRESENTATIVES = 'elected_representatives';
    public const ACCESS_FILES = 'files';
    public const ACCESS_INSTITUTIONAL_EVENTS = 'institutional_events';

    public const ACCESSES = [
        self::ACCESS_MESSAGES,
        self::ACCESS_EVENTS,
        self::ACCESS_ADHERENTS,
    ];

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank(message="Veuillez renseigner un rôle.")
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent", inversedBy="receivedDelegatedAccesses")
     *
     * @Assert\NotBlank(message="Veuillez sélectionner un adhérent.")
     */
    private $delegated;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $accesses;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $scopeFeatures;

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

    public function __construct(UuidInterface $uuid = null)
    {
        $this->restrictedCommittees = new ArrayCollection();
        $this->uuid = $uuid ?? Uuid::uuid4();
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
        $delegated->addReceivedDelegatedAccess($this);
    }

    public function getAccesses(): array
    {
        return $this->accesses ? array_filter($this->accesses) : [];
    }

    public function setAccesses(array $accesses): void
    {
        $this->accesses = $accesses;
    }

    public function getScopeFeatures(): array
    {
        return $this->scopeFeatures ? array_filter($this->scopeFeatures) : [];
    }

    public function setScopeFeatures(array $scopeFeatures): void
    {
        $this->scopeFeatures = $scopeFeatures;
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

    public function removeRestrictedCommittee(Committee $restrictedCommittee)
    {
        $this->restrictedCommittees->removeElement($restrictedCommittee);
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
