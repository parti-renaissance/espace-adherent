<?php

namespace App\Entity\AdherentMandate;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\EntityIdentityTrait;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\ValueObject\Genders;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdherentMandate\AdherentMandateRepository")
 * @ORM\Table(name="adherent_mandate")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "committee": "App\Entity\AdherentMandate\CommitteeAdherentMandate",
 *     "territorial_council": "App\Entity\AdherentMandate\TerritorialCouncilAdherentMandate",
 * })
 */
abstract class AbstractAdherentMandate
{
    public const REASON_ELECTION = 'election';
    public const REASON_COMMITTEE_MERGE = 'committee_merge';
    public const REASON_MANUAL = 'manual';
    public const REASON_REPLACED = 'replaced';

    use EntityIdentityTrait;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent", inversedBy="adherentMandates")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $adherent;

    /**
     * @var string|null
     *
     * @ORM\Column(length=6)
     *
     * @Assert\NotBlank(message="common.gender.invalid_choice")
     * @Assert\Choice(
     *     choices=Genders::MALE_FEMALE,
     *     message="common.gender.invalid_choice",
     *     strict=true
     * )
     */
    protected $gender;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank
     * @Assert\DateTime
     */
    protected $beginAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\DateTime
     */
    protected $finishAt;

    /**
     * @var Committee
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Committee", inversedBy="adherentMandates")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $committee;

    /**
     * @var TerritorialCouncil
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\TerritorialCouncil")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $territorialCouncil;

    /**
     * @var string|null
     *
     * @ORM\Column(length=255, nullable=true)
     *
     * @Assert\NotBlank(message="common.quality.invalid_choice")
     * @Assert\Choice(choices=App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum::POLITICAL_COMMITTEE_ELECTED_MEMBERS, strict=true)
     */
    protected $quality;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $reason;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    public $provisional = false;

    public function __construct(Adherent $adherent, string $gender, \DateTime $beginAt, \DateTime $finishAt = null)
    {
        $this->uuid = Uuid::uuid4();
        $this->adherent = $adherent;
        $this->gender = $gender ?? $adherent->getGender();
        $this->beginAt = $beginAt;
        $this->finishAt = $finishAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getBeginAt(): \DateTime
    {
        return $this->beginAt;
    }

    public function setBeginAt(\DateTime $beginAt): void
    {
        $this->beginAt = $beginAt;
    }

    public function getFinishAt(): ?\DateTime
    {
        return $this->finishAt;
    }

    public function setFinishAt(?\DateTime $finishAt = null): void
    {
        $this->finishAt = $finishAt;
    }

    public function isEnded(): bool
    {
        return null !== $this->getFinishAt();
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function setCommittee(Committee $committee): void
    {
        $this->committee = $committee;
    }

    public function getTerritorialCouncil(): ?TerritorialCouncil
    {
        return $this->territorialCouncil;
    }

    public function setTerritorialCouncil(TerritorialCouncil $territorialCouncil): void
    {
        $this->territorialCouncil = $territorialCouncil;
    }

    public function getQuality(): ?string
    {
        return $this->quality;
    }

    public function setQuality(string $quality): void
    {
        $this->quality = $quality;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }
}
