<?php

namespace App\Entity\AdherentMandate;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\EntityAdherentBlameableTrait;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\ValueObject\Genders;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdherentMandate\AdherentMandateRepository")
 * @ORM\Table(name="adherent_mandate")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "committee": "App\Entity\AdherentMandate\CommitteeAdherentMandate",
 *     "territorial_council": "App\Entity\AdherentMandate\TerritorialCouncilAdherentMandate",
 *     "national_council": "App\Entity\AdherentMandate\NationalCouncilAdherentMandate",
 *     "elected_representative": "App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate"
 * })
 */
abstract class AbstractAdherentMandate implements AdherentMandateInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;
    use EntityAdherentBlameableTrait;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent", inversedBy="adherentMandates")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank
     */
    #[Groups(['elected_mandate_write', 'elected_mandate_read'])]
    protected $adherent;

    /**
     * @var string|null
     *
     * @ORM\Column(length=6, nullable=true)
     *
     * @Assert\NotBlank(message="common.gender.invalid_choice")
     * @Assert\Choice(
     *     choices=Genders::MALE_FEMALE,
     *     message="common.gender.invalid_choice"
     * )
     */
    protected $gender;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank
     */
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'adherent_elect_read'])]
    protected $beginAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'adherent_elect_read'])]
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
     * @ORM\Column(nullable=true)
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

    public function __construct(
        ?Adherent $adherent = null,
        ?string $gender = null,
        ?\DateTime $beginAt = null,
        ?\DateTime $finishAt = null,
        ?string $quality = null,
        bool $isProvisional = false
    ) {
        $this->uuid = Uuid::uuid4();
        $this->adherent = $adherent;
        $this->gender = $gender ?? $adherent?->getGender();
        $this->beginAt = $beginAt;
        $this->finishAt = $finishAt;
        $this->quality = $quality;
        $this->provisional = $isProvisional;
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
        if (!$this->gender) {
            $this->gender = $adherent->getGender();
        }
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

    public function setBeginAt(?\DateTime $beginAt): void
    {
        $this->beginAt = $beginAt ?? new \DateTime();
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

    public function isFemale(): bool
    {
        return Genders::FEMALE === $this->gender;
    }
}
