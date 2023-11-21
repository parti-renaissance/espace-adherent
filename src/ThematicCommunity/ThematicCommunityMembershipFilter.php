<?php

namespace App\ThematicCommunity;

use App\Entity\ThematicCommunity\ThematicCommunity;
use App\Entity\UserListDefinition;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class ThematicCommunityMembershipFilter
{
    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['joinedAt', 'lastName'])]
    private $sort = 'joinedAt';

    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['a', 'd'])]
    private $order = 'd';

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $gender;

    /**
     * @var int
     */
    private $ageMin;

    /**
     * @var int
     */
    private $ageMax;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $role;

    /**
     * @var bool
     */
    private $emailSubscription;

    /**
     * @var bool
     */
    private $smsSubscription;

    /**
     * @var array
     */
    private $thematicCommunities;

    /**
     * @var \DateTime
     */
    private $joinedSince;

    /**
     * @var \DateTime
     */
    private $joinedUntil;

    /**
     * @var array
     */
    private $motivations = [];

    /**
     * @var UserListDefinition[]|Collection
     */
    private $categories;

    /**
     * @var bool
     */
    private $expert;

    /**
     * @var bool
     */
    private $withJob;

    /**
     * @var array
     */
    private $job;

    /**
     * @var bool
     */
    private $withAssociation;

    public function __construct(array $handledCommunities = [])
    {
        $this->thematicCommunities = $handledCommunities;
        $this->categories = new ArrayCollection();
    }

    public function getSort(): string
    {
        return $this->sort;
    }

    public function setSort(string $sort): void
    {
        $this->sort = $sort;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function setOrder(string $order): void
    {
        $this->order = $order;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getAgeMin(): ?int
    {
        return $this->ageMin;
    }

    public function setAgeMin(?int $ageMin): void
    {
        $this->ageMin = $ageMin;
    }

    public function getAgeMax(): ?int
    {
        return $this->ageMax;
    }

    public function setAgeMax(?int $ageMax): void
    {
        $this->ageMax = $ageMax;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getCityAsArray(): array
    {
        return $this->city ? array_map('trim', explode(',', $this->city)) : [];
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): void
    {
        $this->role = $role;
    }

    public function isEmailSubscription(): ?bool
    {
        return $this->emailSubscription;
    }

    public function setEmailSubscription(?bool $emailSubscription): void
    {
        $this->emailSubscription = $emailSubscription;
    }

    public function isSmsSubscription(): ?bool
    {
        return $this->smsSubscription;
    }

    public function setSmsSubscription(?bool $smsSubscription): void
    {
        $this->smsSubscription = $smsSubscription;
    }

    public function getThematicCommunities(): ?array
    {
        return $this->thematicCommunities;
    }

    public function setThematicCommunities(array $thematicCommunities): void
    {
        $this->thematicCommunities = $thematicCommunities;
    }

    public function getJoinedSince(): ?\DateTime
    {
        return $this->joinedSince;
    }

    public function setJoinedSince(?\DateTime $joinedSince): void
    {
        $this->joinedSince = $joinedSince;
    }

    public function getJoinedUntil(): ?\DateTime
    {
        return $this->joinedUntil;
    }

    public function setJoinedUntil(?\DateTime $joinedUntil): void
    {
        $this->joinedUntil = $joinedUntil;
    }

    public function getMotivations(): array
    {
        return $this->motivations;
    }

    public function setMotivations(array $motivations): void
    {
        $this->motivations = $motivations;
    }

    public function getCategories(): ?Collection
    {
        return $this->categories;
    }

    public function setCategories(?Collection $categories): void
    {
        $this->categories = $categories;
    }

    public function isExpert(): ?bool
    {
        return $this->expert;
    }

    public function setExpert(?bool $expert): void
    {
        $this->expert = $expert;
    }

    public function isWithJob(): ?bool
    {
        return $this->withJob;
    }

    public function setWithJob(?bool $withJob): void
    {
        $this->withJob = $withJob;
    }

    public function getJob(): ?array
    {
        return $this->job;
    }

    public function setJob(?array $job): void
    {
        $this->job = $job;
    }

    public function isWithAssociation(): ?bool
    {
        return $this->withAssociation;
    }

    public function setWithAssociation(?bool $withAssociation): void
    {
        $this->withAssociation = $withAssociation;
    }

    public function toArray(): array
    {
        return [
            'sort' => $this->sort,
            'order' => $this->order,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'gender' => $this->gender,
            'ageMin' => $this->ageMin,
            'ageMax' => $this->ageMax,
            'city' => $this->city,
            'country' => $this->country,
            'role' => $this->role,
            'emailSubscription' => $this->emailSubscription,
            'smsSubscription' => $this->smsSubscription,
            'thematicCommunities' => array_map(static function (ThematicCommunity $community) {
                return $community->getId();
            }, $this->thematicCommunities),
            'joinedSince' => $this->joinedSince ? $this->joinedSince->format('Y-m-d') : null,
            'joinedUntil' => $this->joinedUntil ? $this->joinedUntil->format('Y-m-d') : null,
            'motivations' => $this->motivations,
            'categories' => $this->categories->toArray(),
            'expert' => $this->expert,
            'withJob' => $this->withJob,
            'job' => $this->job,
            'withAssociation' => $this->withAssociation,
        ];
    }
}
