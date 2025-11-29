<?php

declare(strict_types=1);

namespace App\Entity\AdherentMandate;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\EntityAdherentBlameableInterface;
use App\Entity\EntityAdherentBlameableTrait;
use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Repository\AdherentMandate\AdherentMandateRepository;
use App\ValueObject\Genders;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(['committee' => CommitteeAdherentMandate::class, 'elected_representative' => ElectedRepresentativeAdherentMandate::class])]
#[ORM\Entity(repositoryClass: AdherentMandateRepository::class)]
#[ORM\Index(columns: ['type'])]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\Table(name: 'adherent_mandate')]
abstract class AbstractAdherentMandate implements AdherentMandateInterface, EntityAdherentBlameableInterface, EntityAdministratorBlameableInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;
    use EntityAdherentBlameableTrait;

    /**
     * @var Adherent
     */
    #[Assert\NotBlank]
    #[Groups(['elected_mandate_write', 'elected_mandate_read'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class, inversedBy: 'adherentMandates')]
    protected $adherent;

    /**
     * @var string|null
     */
    #[Assert\Choice(choices: Genders::MALE_FEMALE, message: 'common.gender.invalid_choice')]
    #[Assert\NotBlank(message: 'common.gender.invalid_choice')]
    #[ORM\Column(length: 6, nullable: true)]
    protected $gender;

    /**
     * @var \DateTime
     */
    #[Assert\NotBlank]
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'adherent_elect_read'])]
    #[ORM\Column(type: 'datetime')]
    protected $beginAt;

    /**
     * @var \DateTime|null
     */
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'adherent_elect_read'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $finishAt;

    /**
     * @var Committee
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Committee::class, inversedBy: 'adherentMandates')]
    protected $committee;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    protected $quality;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $reason;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public $provisional = false;

    public function __construct(
        ?Adherent $adherent = null,
        ?string $gender = null,
        ?\DateTime $beginAt = null,
        ?\DateTime $finishAt = null,
        ?string $quality = null,
        bool $isProvisional = false,
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
