<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DonatorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DonatorRepository::class)]
#[ORM\Index(columns: ['email_address', 'first_name', 'last_name'])]
#[ORM\Table(name: 'donators')]
class Donator implements \Stringable
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * The unique account identifier.
     */
    #[ORM\Column(unique: true)]
    private $identifier;

    /**
     * @var Adherent|null
     */
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private $adherent;

    #[Assert\Length(min: 1, max: 50)]
    #[Assert\NotBlank]
    #[ORM\Column(length: 50, nullable: true)]
    private $lastName;

    #[Assert\Length(min: 2, max: 100)]
    #[Assert\NotBlank]
    #[ORM\Column(length: 100, nullable: true)]
    private $firstName;

    #[Assert\Length(max: 50)]
    #[Assert\NotBlank(message: 'common.birthcity.not_blank')]
    #[ORM\Column(length: 50, nullable: true)]
    private $city;

    #[ORM\Column(length: 2)]
    private $country;

    #[Assert\Email(message: 'common.email.invalid')]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length')]
    #[ORM\Column(nullable: true)]
    private $emailAddress;

    #[ORM\Column(length: 6, nullable: true)]
    private $gender;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $comment;

    /**
     * @var Donation[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'donator', targetEntity: Donation::class, cascade: ['all'])]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $donations;

    /**
     * @var Donation|null
     */
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\OneToOne(targetEntity: Donation::class)]
    private $lastSuccessfulDonation;

    /**
     * @var Donation|null
     */
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\OneToOne(targetEntity: Donation::class)]
    private $referenceDonation;

    #[ORM\ManyToMany(targetEntity: DonatorTag::class)]
    private Collection $tags;

    /**
     * @var DonatorKinship[]|Collection
     */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'donator', targetEntity: DonatorKinship::class, cascade: ['all'])]
    private Collection $kinships;

    #[ORM\OneToMany(mappedBy: 'donator', targetEntity: TaxReceipt::class, cascade: ['all'])]
    private Collection $taxReceipts;

    public function __construct(
        ?string $lastName = null,
        ?string $firstName = null,
        ?string $city = null,
        ?string $country = null,
        ?string $emailAddress = null,
        ?string $gender = null,
    ) {
        $this->uuid = Uuid::uuid4();
        $this->lastName = $lastName;
        $this->firstName = $firstName;
        $this->city = $city;
        $this->country = $country;
        $this->emailAddress = $emailAddress;
        $this->gender = $gender;
        $this->donations = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->kinships = new ArrayCollection();
        $this->taxReceipts = new ArrayCollection();
    }

    public function __toString(): string
    {
        return \sprintf(
            '%s %s (%s)',
            $this->firstName,
            $this->lastName,
            $this->identifier
        );
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function hasIdentifier(): bool
    {
        return (bool) $this->identifier;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function isAdherent(): bool
    {
        return (bool) $this->adherent;
    }

    public function setAdherent(?Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getLastDonationDate(): ?\DateTimeInterface
    {
        if (!$donation = $this->lastSuccessfulDonation) {
            return null;
        }

        return $donation->getCreatedAt();
    }

    /**
     * @return Donation[]|Collection
     */
    public function getDonations(): Collection
    {
        return $this->donations;
    }

    public function addDonation(Donation $donation): void
    {
        if (!$this->donations->contains($donation)) {
            $donation->setDonator($this);

            $this->donations->add($donation);
        }
    }

    public function removeDonation(Donation $donation): void
    {
        $this->donations->removeElement($donation);
    }

    public function getSuccessfulDonations(): ArrayCollection
    {
        $successfulDonations = $this->donations->filter(function (Donation $donation) {
            return null !== $donation->getLastSuccessDate();
        });

        $iterator = $successfulDonations->getIterator();
        $iterator->uasort(function (Donation $donationA, Donation $donationB) {
            return $donationA->getLastSuccessDate() < $donationB->getLastSuccessDate() ? 1 : -1;
        });

        return new ArrayCollection($iterator->getArrayCopy());
    }

    public function computeLastSuccessfulDonation(): void
    {
        $lastSuccessfulDonation = $this->getSuccessfulDonations()->first();

        $this->lastSuccessfulDonation = false !== $lastSuccessfulDonation ? $lastSuccessfulDonation : null;
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(DonatorTag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
    }

    public function removeTag(DonatorTag $tag): void
    {
        $this->tags->removeElement($tag);
    }

    public function getTagsAsString(): string
    {
        return implode(', ', $this->tags->toArray());
    }

    public function getLastSuccessfulDonation(): ?Donation
    {
        return $this->lastSuccessfulDonation;
    }

    public function setLastSuccessfulDonation(?Donation $donation): void
    {
        $this->lastSuccessfulDonation = $donation;

        $this->setMembershipDonation($donation);
    }

    public function setMembershipDonation(Donation $donation): void
    {
        if ($this->adherent && $donation->isMembership()) {
            $this->adherent->donatedForMembership($donation->getDonatedAt());
        }
    }

    public function getReferenceDonation(): ?Donation
    {
        return $this->referenceDonation;
    }

    public function setReferenceDonation(?Donation $donation): void
    {
        $this->referenceDonation = $donation;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getReferenceNationality(): ?string
    {
        if ($donation = $this->referenceDonation) {
            return $donation->getNationality();
        }

        if ($donation = $this->lastSuccessfulDonation) {
            return $donation->getNationality();
        }

        return null;
    }

    public function getKinships(): ?Collection
    {
        return $this->kinships;
    }

    public function addKinship(DonatorKinship $kinship): void
    {
        if (!$this->kinships->contains($kinship)) {
            $kinship->setDonator($this);

            $this->kinships->add($kinship);
        }
    }

    public function removeKinship(DonatorKinship $kinship): void
    {
        $this->kinships->removeElement($kinship);
    }

    public function countSuccessfulDonations(): int
    {
        $count = 0;

        foreach ($this->donations as $donation) {
            if ($donation->isCB() && ($donation->isFinished() || $donation->isSubscriptionInProgress())) {
                foreach ($donation->getTransactions() as $transaction) {
                    if ($transaction->isSuccessful()) {
                        ++$count;
                    }
                }
            } else {
                if ($donation->isFinished()) {
                    ++$count;
                }
            }
        }

        return $count;
    }

    public function getTotalDonated(): int
    {
        $total = 0;

        foreach ($this->donations as $donation) {
            if ($donation->isCB() && ($donation->isFinished() || $donation->isSubscriptionInProgress())) {
                foreach ($donation->getTransactions() as $transaction) {
                    if ($transaction->isSuccessful()) {
                        $total += $donation->getAmountInEuros();
                    }
                }
            } else {
                if ($donation->isFinished()) {
                    $total += $donation->getAmountInEuros();
                }
            }
        }

        return $total;
    }

    public function addTaxReceipt(TaxReceipt $receipt): void
    {
        if (!$this->taxReceipts->contains($receipt)) {
            $this->taxReceipts->add($receipt);
        }
    }
}
