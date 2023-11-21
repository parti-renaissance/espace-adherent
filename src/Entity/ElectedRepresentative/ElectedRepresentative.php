<?php

namespace App\Entity\ElectedRepresentative;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Adherent;
use App\Entity\AuthoredInterface;
use App\Entity\EntityAdherentBlameableInterface;
use App\Entity\EntityAdherentBlameableTrait;
use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\EntityUserListDefinitionTrait;
use App\Entity\Geo\Zone;
use App\Entity\ReferentTag;
use App\Entity\ZoneableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {
 *             "groups": {"elected_representative_read"}
 *         },
 *         "denormalization_context": {
 *             "groups": {"elected_representative_write"}
 *         },
 *         "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'elected_representative')"
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/v3/elected_representatives/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'elected_representative') and is_granted('MANAGE_ELECTED_REPRESENTATIVE', object)"
 *         },
 *         "put": {
 *             "path": "/v3/elected_representatives/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'elected_representative') and is_granted('MANAGE_ELECTED_REPRESENTATIVE', object)"
 *         },
 *         "delete": {
 *             "path": "/v3/elected_representatives/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'elected_representative') and is_granted('MANAGE_ELECTED_REPRESENTATIVE', object)"
 *         }
 *     },
 *     collectionOperations={
 *         "post": {
 *             "path": "/v3/elected_representatives",
 *         }
 *     }
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\ElectedRepresentative\ElectedRepresentativeRepository")
 */
#[UniqueEntity(fields: ['adherent'], message: 'elected_representative.invalid_adherent')]
class ElectedRepresentative implements EntityAdherentBlameableInterface, EntityAdministratorBlameableInterface, ZoneableEntity, AuthoredInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;
    use EntityAdherentBlameableTrait;
    use EntityUserListDefinitionTrait;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     */
    #[Assert\NotBlank]
    #[Assert\Length(max: '50')]
    #[Groups(['elected_representative_change_diff', 'elected_representative_write', 'elected_representative_read', 'elected_representative_list', 'elected_mandate_read'])]
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     */
    #[Assert\NotBlank]
    #[Assert\Length(max: '50')]
    #[Groups(['elected_representative_change_diff', 'elected_representative_write', 'elected_representative_read', 'elected_representative_list', 'elected_mandate_read'])]
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(length=10, nullable=true)
     */
    #[Assert\Choice(callback: ['App\ValueObject\Genders', 'all'], message: 'common.gender.invalid_choice')]
    #[Groups(['elected_representative_change_diff', 'elected_representative_write', 'elected_representative_read', 'elected_representative_list'])]
    private $gender;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     */
    #[Assert\NotBlank]
    #[Assert\Range(min: '-120 years', max: '-18 years', minMessage: "L'élu doit être âgé de moins de 120 ans", maxMessage: "L'age minimum pour être un élu est de 18 ans")]
    #[Groups(['elected_representative_change_diff', 'elected_representative_write', 'elected_representative_read'])]
    private $birthDate;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    #[Assert\Length(max: '255')]
    #[Groups(['elected_representative_write', 'elected_representative_read'])]
    private $birthPlace;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    #[Assert\Email(message: 'common.email.invalid')]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length')]
    #[Groups(['elected_representative_write'])]
    private $contactEmail;

    /**
     * @var PhoneNumber|null
     *
     * @ORM\Column(type="phone_number", nullable=true)
     *
     * @AssertPhoneNumber
     */
    #[Groups(['elected_representative_write', 'elected_representative_read', 'elected_representative_list'])]
    private $contactPhone;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    #[Groups(['elected_representative_write', 'elected_representative_read'])]
    private $hasFollowedTraining = false;

    /**
     * Mailchimp unsubscribed date
     *
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $emailUnsubscribedAt;

    /**
     * Mailchimp unsubscribed status
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $emailUnsubscribed = false;

    /**
     * @ORM\Column(nullable=true)
     */
    #[Groups(['elected_representative_read', 'elected_representative_list'])]
    private ?string $contributionStatus = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    #[Groups(['elected_representative_read', 'elected_representative_list'])]
    private ?\DateTime $contributedAt = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ElectedRepresentative\Contribution")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    #[Groups(['elected_representative_list', 'elected_representative_read'])]
    private ?Contribution $lastContribution = null;

    /**
     * @var Adherent|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    #[Groups(['elected_representative_write', 'elected_representative_read'])]
    private $adherent;

    /**
     * @var SocialNetworkLink[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\ElectedRepresentative\SocialNetworkLink",
     *     mappedBy="electedRepresentative",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     */
    #[Assert\Valid]
    private $socialNetworkLinks;

    /**
     * @var Mandate[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\ElectedRepresentative\Mandate",
     *     mappedBy="electedRepresentative",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    #[Assert\Valid]
    #[Groups(['elected_representative_read'])]
    private $mandates;

    /**
     * PoliticalFunction[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\ElectedRepresentative\PoliticalFunction",
     *     mappedBy="electedRepresentative",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     */
    #[Assert\Valid]
    private $politicalFunctions;

    /**
     * @var ElectedRepresentativeLabel[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\ElectedRepresentative\ElectedRepresentativeLabel",
     *     mappedBy="electedRepresentative",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     */
    #[Assert\Valid]
    private $labels;

    /**
     * Sponsorship[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\ElectedRepresentative\Sponsorship",
     *     mappedBy="electedRepresentative",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     */
    #[Assert\Valid]
    private $sponsorships;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\ElectedRepresentative\Contribution",
     *     mappedBy="electedRepresentative",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EXTRA_LAZY"
     * )
     */
    private Collection $contributions;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\ElectedRepresentative\Payment",
     *     mappedBy="electedRepresentative",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EXTRA_LAZY"
     * )
     * @ORM\OrderBy({"date": "DESC"})
     */
    #[Groups(['elected_representative_read'])]
    private Collection $payments;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\ElectedRepresentative\RevenueDeclaration",
     *     mappedBy="electedRepresentative",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EXTRA_LAZY"
     * )
     * @ORM\OrderBy({"createdAt": "DESC"})
     */
    private Collection $revenueDeclarations;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();

        $this->socialNetworkLinks = new ArrayCollection();
        $this->mandates = new ArrayCollection();
        $this->politicalFunctions = new ArrayCollection();
        $this->labels = new ArrayCollection();
        $this->sponsorships = new ArrayCollection();
        $this->userListDefinitions = new ArrayCollection();
        $this->contributions = new ArrayCollection();
        $this->payments = new ArrayCollection();

        $this->initializeSponsorships();
    }

    public static function create(
        string $firstName,
        string $lastName,
        \DateTime $birthDate,
        string $gender = null,
        UuidInterface $uuid = null
    ): self {
        $electedRepresentative = new self();

        $electedRepresentative->uuid = $uuid ?: Uuid::uuid4();
        $electedRepresentative->firstName = $firstName;
        $electedRepresentative->lastName = $lastName;
        $electedRepresentative->gender = $gender;
        $electedRepresentative->birthDate = $birthDate;

        return $electedRepresentative;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender = null): void
    {
        $this->gender = $gender;
    }

    public function getBirthDate(): ?\DateTime
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTime $birthDate): void
    {
        $this->birthDate = $birthDate;
    }

    public function getBirthPlace(): ?string
    {
        return $this->birthPlace;
    }

    public function setBirthPlace(string $birthPlace = null): void
    {
        $this->birthPlace = $birthPlace;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(string $contactEmail = null): void
    {
        $this->contactEmail = $contactEmail;
    }

    public function getContactPhone(): ?PhoneNumber
    {
        return $this->contactPhone;
    }

    public function setContactPhone(PhoneNumber $contactPhone = null): void
    {
        $this->contactPhone = $contactPhone;
    }

    public function hasFollowedTraining(): ?bool
    {
        return $this->hasFollowedTraining;
    }

    public function setHasFollowedTraining(bool $hasFollowedTraining = null): void
    {
        $this->hasFollowedTraining = $hasFollowedTraining;
    }

    #[Groups(['elected_representative_change_diff'])]
    public function isAdherent(): bool
    {
        return $this->adherent instanceof Adherent;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(Adherent $adherent = null): void
    {
        $this->adherent = $adherent;
    }

    public function removeAdherent(): void
    {
        $this->adherent = null;
    }

    public function getSocialNetworkLinks(): Collection
    {
        return $this->socialNetworkLinks;
    }

    public function addSocialNetworkLink(SocialNetworkLink $link): void
    {
        if (!$this->socialNetworkLinks->contains($link)) {
            $link->setElectedRepresentative($this);
            $this->socialNetworkLinks->add($link);
        }
    }

    public function removeSocialNetworkLink(SocialNetworkLink $link): void
    {
        $this->socialNetworkLinks->removeElement($link);
    }

    public function getMandates(): Collection
    {
        return $this->mandates;
    }

    /**
     * @return Mandate[]
     */
    #[Groups(['elected_representative_list'])]
    public function getCurrentMandates(): array
    {
        $now = new \DateTime();

        return $this->mandates->filter(function (Mandate $mandate) use ($now) {
            return $mandate->isElected() && $mandate->isOnGoing() && (null === $mandate->getFinishAt() || $mandate->getFinishAt() > $now);
        })->getValues();
    }

    /**
     * @return Mandate[]|Collection
     */
    public function getFinishedMandates(): Collection
    {
        return $this->mandates->filter(function (Mandate $mandate) {
            return $mandate->isElected() && !$mandate->isOnGoing();
        });
    }

    public function getElectedMandates(): Collection
    {
        return $this->mandates->filter(function (Mandate $mandate) {
            return $mandate->isElected();
        });
    }

    public function getNotElectedMandates(): Collection
    {
        return $this->mandates->filter(function (Mandate $mandate) {
            return !$mandate->isElected();
        });
    }

    public function addMandate(Mandate $mandate): void
    {
        if (!$this->mandates->contains($mandate)) {
            $mandate->setElectedRepresentative($this);
            $this->mandates->add($mandate);
        }
    }

    public function removeMandate(Mandate $mandate): void
    {
        $this->mandates->removeElement($mandate);
    }

    public function getPoliticalFunctions(): Collection
    {
        return $this->politicalFunctions;
    }

    /**
     * @return PoliticalFunction[]
     */
    #[Groups(['elected_representative_list'])]
    public function getCurrentPoliticalFunctions(): array
    {
        return array_merge(...array_map(function (Mandate $mandate) {
            return array_filter($mandate->getPoliticalFunctions()->toArray(), function (PoliticalFunction $politicalFunction) {
                return $politicalFunction->isOnGoing() && null === $politicalFunction->getFinishAt();
            });
        }, $this->getCurrentMandates()));
    }

    public function addPoliticalFunction(PoliticalFunction $politicalFunction): void
    {
        if (!$this->politicalFunctions->contains($politicalFunction)) {
            $politicalFunction->setElectedRepresentative($this);
            $this->politicalFunctions->add($politicalFunction);
        }
    }

    public function removePoliticalFunction(PoliticalFunction $politicalFunction): void
    {
        $this->politicalFunctions->removeElement($politicalFunction);
    }

    public function exportIsAdherent(): string
    {
        return $this->isAdherent() ? 'oui' : 'non';
    }

    public function exportMandates(): string
    {
        return implode(', ', $this->getCurrentMandates()->toArray());
    }

    public function exportPoliticalFunctions(): string
    {
        return implode(', ', $this->getCurrentPoliticalFunctions());
    }

    /**
     * @return ElectedRepresentativeLabel[]|Collection
     */
    public function getLabels(): Collection
    {
        return $this->labels;
    }

    /**
     * @return ElectedRepresentativeLabel[]|Collection
     */
    public function getCurrentLabels(): Collection
    {
        return $this->labels->filter(function (ElectedRepresentativeLabel $label) {
            return $label->isOnGoing();
        });
    }

    public function getSortedLabels(): Collection
    {
        $iterator = $this->labels->getIterator();
        $iterator->uasort(function (ElectedRepresentativeLabel $labelA, ElectedRepresentativeLabel $labelB) {
            return $labelB->isOnGoing() <=> $labelA->isOnGoing();
        });

        return new ArrayCollection($iterator->getArrayCopy());
    }

    public function getLabel(string $name): ?ElectedRepresentativeLabel
    {
        foreach ($this->labels as $label) {
            if ($name === $label->getName()) {
                return $label;
            }
        }

        return null;
    }

    public function addLabel(ElectedRepresentativeLabel $label): void
    {
        if (!$this->labels->contains($label)) {
            $label->setElectedRepresentative($this);
            $this->labels->add($label);
        }
    }

    public function removeLabel(ElectedRepresentativeLabel $label): void
    {
        $this->labels->removeElement($label);
    }

    public function getSponsorships(): Collection
    {
        if (0 === $this->sponsorships->count()) {
            $this->initializeSponsorships();
        }

        return $this->sponsorships;
    }

    public function addSponsorship(Sponsorship $sponsorship): void
    {
        if (!$this->sponsorships->contains($sponsorship)) {
            $sponsorship->setElectedRepresentative($this);
            $this->sponsorships->add($sponsorship);
        }
    }

    public function removeSponsorship(Sponsorship $sponsorship): void
    {
        $this->sponsorships->removeElement($sponsorship);
    }

    private function initializeSponsorships(): void
    {
        foreach (Sponsorship::getYears() as $year) {
            $sponsorship = new Sponsorship($year, null, $this);
            $this->sponsorships->add($sponsorship);
        }
    }

    public function getFullName(): string
    {
        return sprintf('%s %s', $this->firstName, $this->lastName);
    }

    public function getAge(): ?int
    {
        return $this->birthDate ? $this->birthDate->diff(new \DateTime())->y : null;
    }

    #[Groups(['elected_representative_change_diff', 'elected_representative_read'])]
    public function getEmailAddress(): ?string
    {
        if ($this->adherent) {
            return $this->adherent->getEmailAddress();
        }

        if ($this->contactEmail) {
            return $this->contactEmail;
        }

        return null;
    }

    public function subscribeEmails(): void
    {
        $this->emailUnsubscribed = false;
    }

    public function unsubscribeEmails(): void
    {
        $this->emailUnsubscribed = true;
        $this->emailUnsubscribedAt = new \DateTime();
    }

    public function isEmailUnsubscribed(): bool
    {
        return $this->emailUnsubscribed;
    }

    public function __toString(): string
    {
        return sprintf('%s %s', $this->firstName, $this->lastName);
    }

    /**
     * @return ReferentTag[]|ArrayCollection
     */
    public function getActiveReferentTags(): ArrayCollection
    {
        $activeTags = new ArrayCollection();

        foreach ($this->getCurrentMandates() as $mandate) {
            if (!$zone = $mandate->getZone()) {
                continue;
            }

            foreach ($zone->getReferentTags() as $referentTag) {
                if (!$activeTags->contains($referentTag)) {
                    $activeTags->add($referentTag);
                }
            }
        }

        return $activeTags;
    }

    public function getActiveReferentTagCodes(): array
    {
        $tags = [];

        foreach ($this->getActiveReferentTags() as $referentTag) {
            $tags[] = $referentTag->getCode();
        }

        return array_unique($tags);
    }

    public function getZones(): Collection
    {
        return new ArrayCollection(
            array_values(
                array_filter(
                    array_map(
                        fn (Mandate $mandate) => $mandate->getGeoZone(),
                        $this->getCurrentMandates()
                    )
                )
            )
        );
    }

    public function addZone(Zone $Zone): void
    {
    }

    public function removeZone(Zone $zone): void
    {
    }

    public function clearZones(): void
    {
    }

    public function getAuthor(): ?Adherent
    {
        return $this->createdByAdherent;
    }

    public function getContributionStatus(): ?string
    {
        return $this->contributionStatus;
    }

    public function setContributionStatus(?string $contributionStatus): void
    {
        $this->contributionStatus = $contributionStatus;
    }

    public function getContributedAt(): ?\DateTime
    {
        return $this->contributedAt;
    }

    public function setContributedAt(?\DateTime $contributedAt): void
    {
        $this->contributedAt = $contributedAt;
    }

    public function getLastContribution(): ?Contribution
    {
        return $this->lastContribution;
    }

    public function setLastContribution(?Contribution $lastContribution): void
    {
        $this->lastContribution = $lastContribution;
    }

    public function getContributions(): Collection
    {
        return $this->contributions;
    }

    public function addContribution(Contribution $contribution): void
    {
        if (!$this->contributions->contains($contribution)) {
            $contribution->electedRepresentative = $this;
            $this->contributions->add($contribution);
        }
    }

    public function removeContribution(Contribution $contribution): void
    {
        $this->contributions->removeElement($contribution);
    }

    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): void
    {
        if (!$this->payments->contains($payment)) {
            $payment->electedRepresentative = $this;
            $this->payments->add($payment);
        }
    }

    public function removePayment(Payment $payment): void
    {
        $this->payments->removeElement($payment);
    }

    public function getPaymentByOhmeId(string $ohmeId): ?Payment
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('ohmeId', $ohmeId))
        ;

        return $this->payments->matching($criteria)->count() > 0
            ? $this->payments->matching($criteria)->first()
            : null;
    }

    public function getRevenueDeclarations(): Collection
    {
        return $this->revenueDeclarations;
    }

    public function addRevenueDeclaration(int $amount): void
    {
        $this->revenueDeclarations->add(RevenueDeclaration::create($this, $amount));
    }
}
