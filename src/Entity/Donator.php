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
 *     }
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
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Email(message="common.email.invalid")
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     */
    private $emailAddress;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $lastDonationAt;

    /**
     * @ORM\OneToMany(targetEntity="Donation", mappedBy="donator", cascade={"all"})
     */
    private $donations;

    public function __construct(
        ?string $lastName,
        ?string $firstName,
        ?string $city,
        string $country,
        string $emailAddress
    ) {
        $this->lastName = $lastName;
        $this->firstName = $firstName;
        $this->city = $city;
        $this->country = $country;
        $this->emailAddress = $emailAddress;
        $this->donations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifier(): string
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

    public function getLastDonationAt(): ?\DateTimeInterface
    {
        return $this->lastDonationAt;
    }

    public function setLastDonationAt(?\DateTimeInterface $lastDonationAt): void
    {
        $this->lastDonationAt = $lastDonationAt;
    }

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
}
