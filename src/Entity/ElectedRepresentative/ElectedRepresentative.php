<?php

declare(strict_types=1);

namespace App\Entity\ElectedRepresentative;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Adherent;
use App\Entity\AuthoredInterface;
use App\Entity\EntityAdherentBlameableInterface;
use App\Entity\EntityAdherentBlameableTrait;
use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Geo\Zone;
use App\Entity\ZoneableEntityInterface;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use App\ValueObject\Genders;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\QueryBuilder;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/v3/elected_representatives/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'elected_representative') and is_granted('MANAGE_ELECTED_REPRESENTATIVE', object)"
        ),
        new Put(
            uriTemplate: '/v3/elected_representatives/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'elected_representative') and is_granted('MANAGE_ELECTED_REPRESENTATIVE', object)"
        ),
        new Delete(
            uriTemplate: '/v3/elected_representatives/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'elected_representative') and is_granted('MANAGE_ELECTED_REPRESENTATIVE', object)"
        ),
        new Post(uriTemplate: '/v3/elected_representatives'),
    ],
    normalizationContext: ['groups' => ['elected_representative_read']],
    denormalizationContext: ['groups' => ['elected_representative_write']],
    security: "is_granted('REQUEST_SCOPE_GRANTED', 'elected_representative')"
)]
#[ORM\Entity(repositoryClass: ElectedRepresentativeRepository::class)]
#[UniqueEntity(fields: ['adherent'], message: 'elected_representative.invalid_adherent')]
class ElectedRepresentative implements \Stringable, EntityAdherentBlameableInterface, EntityAdministratorBlameableInterface, ZoneableEntityInterface, AuthoredInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;
    use EntityAdherentBlameableTrait;

    /**
     * @var string
     */
    #[Assert\Length(max: 50)]
    #[Assert\NotBlank]
    #[Groups(['elected_representative_change_diff', 'elected_representative_write', 'elected_representative_read', 'elected_representative_list', 'elected_mandate_read'])]
    #[ORM\Column(length: 50)]
    private $lastName;

    /**
     * @var string
     */
    #[Assert\Length(max: 50)]
    #[Assert\NotBlank]
    #[Groups(['elected_representative_change_diff', 'elected_representative_write', 'elected_representative_read', 'elected_representative_list', 'elected_mandate_read'])]
    #[ORM\Column(length: 50)]
    private $firstName;

    /**
     * @var string
     */
    #[Assert\Choice(callback: [Genders::class, 'all'], message: 'common.gender.invalid_choice')]
    #[Groups(['elected_representative_change_diff', 'elected_representative_write', 'elected_representative_read', 'elected_representative_list'])]
    #[ORM\Column(length: 10, nullable: true)]
    private $gender;

    /**
     * @var \DateTime
     */
    #[Assert\NotBlank]
    #[Assert\Range(minMessage: "L'élu doit être âgé de moins de 120 ans", min: '-120 years')]
    #[Assert\Range(maxMessage: "L'age minimum pour être un élu est de 18 ans", max: '-18 years')]
    #[Groups(['elected_representative_change_diff', 'elected_representative_write', 'elected_representative_read'])]
    #[ORM\Column(type: 'date')]
    private $birthDate;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255)]
    #[Groups(['elected_representative_write', 'elected_representative_read'])]
    #[ORM\Column(nullable: true)]
    private $birthPlace;

    /**
     * @var string|null
     */
    #[Assert\Email(message: 'common.email.invalid')]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length')]
    #[Groups(['elected_representative_write'])]
    #[ORM\Column(nullable: true)]
    private $contactEmail;

    /**
     * @var PhoneNumber|null
     */
    #[AssertPhoneNumber]
    #[Groups(['elected_representative_write', 'elected_representative_read', 'elected_representative_list'])]
    #[ORM\Column(type: 'phone_number', nullable: true)]
    private $contactPhone;

    #[Groups(['elected_representative_write', 'elected_representative_read'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $hasFollowedTraining = false;

    /**
     * Mailchimp unsubscribed date
     *
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $emailUnsubscribedAt;

    /**
     * Mailchimp unsubscribed status
     *
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $emailUnsubscribed = false;

    #[Groups(['elected_representative_read', 'elected_representative_list'])]
    #[ORM\Column(nullable: true)]
    private ?string $contributionStatus = null;

    #[Groups(['elected_representative_read', 'elected_representative_list'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $contributedAt = null;

    #[Groups(['elected_representative_list', 'elected_representative_read'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\OneToOne(targetEntity: Contribution::class)]
    private ?Contribution $lastContribution = null;

    /**
     * @var Adherent|null
     */
    #[Groups(['elected_representative_write', 'elected_representative_read'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\OneToOne(targetEntity: Adherent::class)]
    private $adherent;

    /**
     * @var SocialNetworkLink[]|Collection
     */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'electedRepresentative', targetEntity: SocialNetworkLink::class, cascade: ['all'], fetch: 'EAGER', orphanRemoval: true)]
    private $socialNetworkLinks;

    /**
     * @var Mandate[]|Collection
     */
    #[Assert\Valid]
    #[Groups(['elected_representative_read'])]
    #[ORM\OneToMany(mappedBy: 'electedRepresentative', targetEntity: Mandate::class, cascade: ['all'], orphanRemoval: true)]
    private $mandates;

    /**
     * PoliticalFunction[]|Collection
     */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'electedRepresentative', targetEntity: PoliticalFunction::class, cascade: ['all'], fetch: 'EAGER', orphanRemoval: true)]
    private $politicalFunctions;

    /**
     * @var ElectedRepresentativeLabel[]|Collection
     */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'electedRepresentative', targetEntity: ElectedRepresentativeLabel::class, cascade: ['all'], fetch: 'EAGER', orphanRemoval: true)]
    private $labels;

    /**
     * Sponsorship[]|Collection
     */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'electedRepresentative', targetEntity: Sponsorship::class, cascade: ['all'], fetch: 'EAGER', orphanRemoval: true)]
    private $sponsorships;

    #[ORM\OneToMany(mappedBy: 'electedRepresentative', targetEntity: Contribution::class, cascade: ['all'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private Collection $contributions;

    #[Groups(['elected_representative_read'])]
    #[ORM\OneToMany(mappedBy: 'electedRepresentative', targetEntity: Payment::class, cascade: ['all'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\OrderBy(['date' => 'DESC'])]
    private Collection $payments;

    #[ORM\OneToMany(mappedBy: 'electedRepresentative', targetEntity: RevenueDeclaration::class, cascade: ['all'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $revenueDeclarations;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();

        $this->socialNetworkLinks = new ArrayCollection();
        $this->mandates = new ArrayCollection();
        $this->politicalFunctions = new ArrayCollection();
        $this->labels = new ArrayCollection();
        $this->sponsorships = new ArrayCollection();
        $this->contributions = new ArrayCollection();
        $this->payments = new ArrayCollection();

        $this->initializeSponsorships();
    }

    public static function create(
        string $firstName,
        string $lastName,
        \DateTime $birthDate,
        ?string $gender = null,
        ?UuidInterface $uuid = null,
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

    public function setGender(?string $gender = null): void
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

    public function setBirthPlace(?string $birthPlace = null): void
    {
        $this->birthPlace = $birthPlace;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(?string $contactEmail = null): void
    {
        $this->contactEmail = $contactEmail;
    }

    public function getContactPhone(): ?PhoneNumber
    {
        return $this->contactPhone;
    }

    public function setContactPhone(?PhoneNumber $contactPhone = null): void
    {
        $this->contactPhone = $contactPhone;
    }

    public function hasFollowedTraining(): ?bool
    {
        return $this->hasFollowedTraining;
    }

    public function setHasFollowedTraining(?bool $hasFollowedTraining = null): void
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

    public function setAdherent(?Adherent $adherent = null): void
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
        return \sprintf('%s %s', $this->firstName, $this->lastName);
    }

    public function getAge(): ?int
    {
        return $this->birthDate?->diff(new \DateTime())->y;
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
        return \sprintf('%s %s', $this->firstName, $this->lastName);
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

    public function addZone(Zone $zone): void
    {
    }

    public function removeZone(Zone $zone): void
    {
    }

    public function clearZones(): void
    {
    }

    public static function getZonesPropertyName(): string
    {
        return '';
    }

    public static function alterQueryBuilderForZones(QueryBuilder $queryBuilder, string $rootAlias): void
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
