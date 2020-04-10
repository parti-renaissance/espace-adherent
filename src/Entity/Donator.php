<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     name="donators",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="donator_identifier_unique", columns="identifier"),
 *     },
 *     indexes={
 *         @ORM\Index(columns={"email_address", "first_name", "last_name"}),
 *     },
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DonatorRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Donator
{
    /**
     * The unique auto incremented primary key.
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * The unique account identifier.
     *
     * @ORM\Column(unique=true)
     */
    private $identifier;

    /**
     * @ORM\ManyToOne(targetEntity="Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $adherent;

    /**
     * @ORM\Column(length=50, nullable=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=2,
     *     max=50,
     * )
     */
    private $lastName;

    /**
     * @ORM\Column(length=100, nullable=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=2,
     *     max=100,
     * )
     */
    private $firstName;

    /**
     * @ORM\Column(length=50, nullable=true)
     *
     * @Assert\NotBlank(message="common.birthcity.not_blank")
     * @Assert\Length(max=50)
     */
    private $city;

    /**
     * @ORM\Column(length=2)
     */
    private $country = 'FR';

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Email(message="common.email.invalid")
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     */
    private $emailAddress;

    /**
     * @ORM\Column(length=6, nullable=true)
     */
    private $gender;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @var Donation[]|Collection
     *
     * @ORM\OneToMany(targetEntity="Donation", mappedBy="donator", cascade={"all"})
     * @ORM\OrderBy({"createdAt": "DESC"})
     */
    private $donations;

    /**
     * @var Donation|null
     *
     * @ORM\OneToOne(targetEntity="Donation")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $lastSuccessfulDonation;

    /**
     * @var Donation|null
     *
     * @ORM\OneToOne(targetEntity="Donation")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $referenceDonation;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\DonatorTag")
     */
    private $tags;

    /**
     * @var DonatorKinship[]|Collection
     *
     * @ORM\OneToMany(targetEntity=DonatorKinship::class, mappedBy="donator", cascade={"all"})
     *
     * @Assert\Valid
     */
    private $kinships;

    public function __construct(
        string $lastName = null,
        string $firstName = null,
        string $city = null,
        string $country = null,
        string $emailAddress = null,
        string $gender = null
    ) {
        $this->lastName = $lastName;
        $this->firstName = $firstName;
        $this->city = $city;
        $this->country = $country;
        $this->emailAddress = $emailAddress;
        $this->gender = $gender;
        $this->donations = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->kinships = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf(
            '%s %s (%s)',
            $this->firstName,
            $this->lastName,
            $this->identifier
        );
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLastDonationAmount(): ?float
    {
        if (!$donation = $this->lastSuccessfulDonation) {
            return null;
        }

        return $donation->getAmountInEuros();
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

    public function getReferenceAddress(): ?string
    {
        if ($donation = $this->referenceDonation) {
            return $donation->getInlineFormattedAddress();
        }

        if ($donation = $this->lastSuccessfulDonation) {
            return $donation->getInlineFormattedAddress();
        }

        return null;
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

    public function countSuccessfullDonations(): int
    {
        $count = 0;

        foreach ($this->donations as $donation) {
            if ($donation->isCB()) {
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
            if ($donation->isCB()) {
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
}
