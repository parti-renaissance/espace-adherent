<?php

namespace App\Entity\AdherentMandate;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\ValueObject\Genders;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="adherent_mandate")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "committee": "App\Entity\AdherentMandate\CommitteeAdherentMandate",
 *     "territorial_council": "App\Entity\AdherentMandate\TerritorialCouncilAdherentMandate",
 * })
 *
 * @Algolia\Index(autoIndex=false)
 */
abstract class AbstractAdherentMandate
{
    const COMMITTEE_TYPE = 'committee';
    const TERRITORIAL_COUNCIL_TYPE = 'territorial_council';

    use EntityIdentityTrait;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
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
}
