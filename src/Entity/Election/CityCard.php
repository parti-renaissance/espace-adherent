<?php

namespace App\Entity\Election;

use App\Entity\City;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'election_city_card')]
#[UniqueEntity(fields: ['city'])]
class CityCard
{
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_MEDIUM = 'medium';

    public const PRIORITY_CHOICES = [
        self::PRIORITY_HIGH,
        self::PRIORITY_MEDIUM,
    ];

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $population;

    /**
     * @var string|null
     */
    #[Assert\Choice(choices: CityCard::PRIORITY_CHOICES)]
    #[ORM\Column(nullable: true)]
    private $priority;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $risk;

    /**
     * @var City
     */
    #[ORM\JoinColumn(unique: true, nullable: false, onDelete: 'CASCADE')]
    #[ORM\OneToOne(targetEntity: City::class)]
    private $city;

    /**
     * @var CityCandidate|null
     */
    #[Assert\Valid]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\OneToOne(targetEntity: CityCandidate::class, cascade: ['all'], orphanRemoval: true)]
    private $firstCandidate;

    /**
     * @var CityManager|null
     */
    #[Assert\Valid]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\OneToOne(targetEntity: CityManager::class, cascade: ['all'], orphanRemoval: true)]
    private $headquartersManager;

    /**
     * @var CityManager|null
     */
    #[Assert\Valid]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\OneToOne(targetEntity: CityManager::class, cascade: ['all'], orphanRemoval: true)]
    private $politicManager;

    /**
     * @var CityManager|null
     */
    #[Assert\Valid]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\OneToOne(targetEntity: CityManager::class, cascade: ['all'], orphanRemoval: true)]
    private $taskForceManager;

    /**
     * @var CityPrevision|null
     */
    #[Assert\Valid]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\OneToOne(targetEntity: CityPrevision::class, cascade: ['all'], orphanRemoval: true)]
    private $candidateOptionPrevision;

    /**
     * @var CityPrevision|null
     */
    #[Assert\Valid]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\OneToOne(targetEntity: CityPrevision::class, cascade: ['all'], orphanRemoval: true)]
    private $preparationPrevision;

    /**
     * @var CityPrevision|null
     */
    #[Assert\Valid]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\OneToOne(targetEntity: CityPrevision::class, cascade: ['all'], orphanRemoval: true)]
    private $thirdOptionPrevision;

    /**
     * @var CityPrevision|null
     */
    #[Assert\Valid]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\OneToOne(targetEntity: CityPrevision::class, cascade: ['all'], orphanRemoval: true)]
    private $candidatePrevision;

    /**
     * @var CityPrevision|null
     */
    #[Assert\Valid]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\OneToOne(targetEntity: CityPrevision::class, cascade: ['all'], orphanRemoval: true)]
    private $nationalPrevision;

    /**
     * @var CityPartner[]|Collection
     */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'city', targetEntity: CityPartner::class, cascade: ['all'], orphanRemoval: true)]
    private $partners;

    /**
     * @var CityContact[]|Collection
     */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'city', targetEntity: CityContact::class, cascade: ['all'], orphanRemoval: true)]
    private $contacts;

    public function __construct(
        ?City $city = null,
        ?int $population = null,
        ?string $priority = null,
        ?bool $risk = false
    ) {
        $this->city = $city;
        $this->population = $population;
        $this->priority = $priority;
        $this->risk = $risk;
        $this->partners = new ArrayCollection();
        $this->contacts = new ArrayCollection();
    }

    public function __toString()
    {
        if (!$this->city) {
            return '';
        }

        return sprintf('%s (%s)', $this->city->getName(), $this->city->getInseeCode());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPopulation(): ?int
    {
        return $this->population;
    }

    public function setPopulation(?int $population): void
    {
        $this->population = $population;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(?string $priority): void
    {
        $this->priority = $priority;
    }

    public function hasRisk(): bool
    {
        return $this->risk;
    }

    public function setRisk(bool $risk): void
    {
        $this->risk = $risk;
    }

    public function getCity(): City
    {
        return $this->city;
    }

    public function getFirstCandidate(): ?CityCandidate
    {
        return $this->firstCandidate;
    }

    public function setFirstCandidate(?CityCandidate $firstCandidate): void
    {
        $this->firstCandidate = $firstCandidate;
    }

    public function removeFirstCandidate(): void
    {
        $this->firstCandidate = null;
    }

    public function getHeadquartersManager(): ?CityManager
    {
        return $this->headquartersManager;
    }

    public function setHeadquartersManager(?CityManager $headquartersManager): void
    {
        $this->headquartersManager = $headquartersManager;
    }

    public function removeHeadquartersManager(): void
    {
        $this->headquartersManager = null;
    }

    public function getPoliticManager(): ?CityManager
    {
        return $this->politicManager;
    }

    public function setPoliticManager(?CityManager $politicManager): void
    {
        $this->politicManager = $politicManager;
    }

    public function removePoliticManager(): void
    {
        $this->politicManager = null;
    }

    public function getTaskForceManager(): ?CityManager
    {
        return $this->taskForceManager;
    }

    public function setTaskForceManager(?CityManager $taskForceManager): void
    {
        $this->taskForceManager = $taskForceManager;
    }

    public function removeTaskForceManager(): void
    {
        $this->taskForceManager = null;
    }

    public function getCandidateOptionPrevision(): ?CityPrevision
    {
        return $this->candidateOptionPrevision;
    }

    public function setCandidateOptionPrevision(?CityPrevision $candidateOptionPrevision): void
    {
        $this->candidateOptionPrevision = $candidateOptionPrevision;
    }

    public function removeCandidateOptionPrevision(): void
    {
        $this->candidateOptionPrevision = null;
    }

    public function getPreparationPrevision(): ?CityPrevision
    {
        return $this->preparationPrevision;
    }

    public function setPreparationPrevision(?CityPrevision $preparationPrevision): void
    {
        $this->preparationPrevision = $preparationPrevision;
    }

    public function removePreparationPrevision(): void
    {
        $this->preparationPrevision = null;
    }

    public function getThirdOptionPrevision(): ?CityPrevision
    {
        return $this->thirdOptionPrevision;
    }

    public function setThirdOptionPrevision(?CityPrevision $thirdOptionPrevision): void
    {
        $this->thirdOptionPrevision = $thirdOptionPrevision;
    }

    public function removeThirdOptionPrevision(): void
    {
        $this->thirdOptionPrevision = null;
    }

    public function getCandidatePrevision(): ?CityPrevision
    {
        return $this->candidatePrevision;
    }

    public function setCandidatePrevision(?CityPrevision $candidatePrevision): void
    {
        $this->candidatePrevision = $candidatePrevision;
    }

    public function removeCandidatePrevision(): void
    {
        $this->candidatePrevision = null;
    }

    public function getNationalPrevision(): ?CityPrevision
    {
        return $this->nationalPrevision;
    }

    public function setNationalPrevision(?CityPrevision $nationalPrevision): void
    {
        $this->nationalPrevision = $nationalPrevision;
    }

    public function removeNationalPrevision(): void
    {
        $this->nationalPrevision = null;
    }

    public function getPartners(): Collection
    {
        return $this->partners;
    }

    public function addPartner(CityPartner $partner): void
    {
        if (!$this->partners->contains($partner)) {
            $partner->setCity($this);
            $this->partners->add($partner);
        }
    }

    public function removePartner(CityPartner $partner): void
    {
        $this->partners->removeElement($partner);
    }

    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(CityContact $contact): void
    {
        if (!$this->contacts->contains($contact)) {
            $contact->setCity($this);
            $this->contacts->add($contact);
        }
    }

    public function removeContact(CityContact $contact): void
    {
        $this->contacts->removeElement($contact);
    }

    public function hasAllContactsDone(): ?bool
    {
        if ($this->contacts->isEmpty()) {
            return null;
        }

        foreach ($this->contacts as $contact) {
            if (!$contact->isDone()) {
                return false;
            }
        }

        return true;
    }
}
