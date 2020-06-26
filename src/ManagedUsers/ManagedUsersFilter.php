<?php

namespace App\ManagedUsers;

use App\Entity\Committee;
use App\Entity\ReferentTag;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Provides a way to handle the search parameters.
 */
class ManagedUsersFilter
{
    /**
     * @var string|null
     */
    private $gender;

    /**
     * @var int|null
     */
    private $ageMin;

    /**
     * @var int|null
     */
    private $ageMax;

    /**
     * @var string|null
     *
     * @Assert\Length(max=255)
     */
    private $firstName;

    /**
     * @var string|null
     *
     * @Assert\Length(max=255)
     */
    private $lastName;

    /**
     * @var string|null
     *
     * @Assert\Length(max=255)
     */
    private $city;

    /**
     * @var array|null
     */
    private $interests = [];

    /**
     * @var \DateTime|null
     */
    private $registeredSince;

    /**
     * @var \DateTime|null
     */
    private $registeredUntil;

    /**
     * @var bool
     */
    private $includeAdherentsNoCommittee = true;

    /**
     * @var bool
     */
    private $includeAdherentsInCommittee = true;

    /**
     * @var bool
     */
    private $includeCommitteeSupervisors;

    /**
     * @var bool
     */
    private $includeCommitteeHosts;

    /**
     * @var bool
     */
    private $includeCitizenProjectHosts;

    /**
     * @var ReferentTag[]
     *
     * @Assert\NotNull
     */
    private $referentTags;

    /**
     * @var bool|null
     */
    private $emailSubscription;

    /**
     * @var string|null
     */
    private $subscriptionType;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices={"createdAt", "lastName"})
     */
    private $sort = 'createdAt';

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices={"a", "d"})
     */
    private $order = 'd';

    /**
     * @var Committee|null
     */
    private $committee;

    /**
     * @var string[]
     */
    private $committeeUuids;

    /**
     * @var string[]
     */
    private $cities;

    /**
     * @var bool|null
     */
    private $voteInCommittee;

    public function __construct(
        string $subscriptionType = null,
        array $referentTags = [],
        array $committeeUuids = [],
        array $cities = []
    ) {
        if (empty($referentTags)) {
            throw new \InvalidArgumentException('Referent tags could not be empty');
        }

        $this->subscriptionType = $subscriptionType;
        $this->referentTags = $referentTags;
        $this->committeeUuids = $committeeUuids;
        $this->cities = $cities;
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

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getInterests(): ?array
    {
        return $this->interests;
    }

    public function setInterests(?array $interests): void
    {
        $this->interests = $interests;
    }

    public function getCityAsArray(): array
    {
        return $this->city ? array_map('trim', explode(',', $this->city)) : [];
    }

    public function getRegisteredSince(): ?\DateTime
    {
        return $this->registeredSince;
    }

    public function setRegisteredSince(?\DateTime $registeredSince): void
    {
        $this->registeredSince = $registeredSince;
    }

    public function getRegisteredUntil(): ?\DateTime
    {
        return $this->registeredUntil;
    }

    public function setRegisteredUntil(?\DateTime $registeredUntil): void
    {
        $this->registeredUntil = $registeredUntil;
    }

    public function includeAdherentsNoCommittee(): bool
    {
        return $this->includeAdherentsNoCommittee;
    }

    public function setIncludeAdherentsNoCommittee(bool $value): void
    {
        $this->includeAdherentsNoCommittee = $value;
    }

    public function includeAdherentsInCommittee(): bool
    {
        return $this->includeAdherentsInCommittee;
    }

    public function setIncludeAdherentsInCommittee(bool $value): void
    {
        $this->includeAdherentsInCommittee = $value;
    }

    public function includeCommitteeSupervisors(): ?bool
    {
        return $this->includeCommitteeSupervisors;
    }

    public function setIncludeCommitteeSupervisors(?bool $value): void
    {
        $this->includeCommitteeSupervisors = $value;
    }

    public function includeCommitteeHosts(): ?bool
    {
        return $this->includeCommitteeHosts;
    }

    public function setIncludeCommitteeHosts(?bool $value): void
    {
        $this->includeCommitteeHosts = $value;
    }

    public function includeCitizenProjectHosts(): ?bool
    {
        return $this->includeCitizenProjectHosts;
    }

    public function setIncludeCitizenProjectHosts(?bool $value): void
    {
        $this->includeCitizenProjectHosts = $value;
    }

    /**
     * @return ReferentTag[]
     */
    public function getReferentTags(): array
    {
        return $this->referentTags;
    }

    public function addReferentTag(ReferentTag $referentTag): void
    {
        $this->referentTags[] = $referentTag;
    }

    public function removeReferentTag(ReferentTag $referentTag): void
    {
        foreach ($this->referentTags as $key => $tag) {
            if ($tag->getId() === $referentTag->getId()) {
                unset($this->referentTags[$key]);
            }
        }

        $this->referentTags = array_values($this->referentTags);
    }

    public function getEmailSubscription(): ?bool
    {
        return $this->emailSubscription;
    }

    public function setEmailSubscription(?bool $emailSubscription): void
    {
        $this->emailSubscription = $emailSubscription;
    }

    public function getVoteInCommittee(): ?bool
    {
        return $this->voteInCommittee;
    }

    public function setVoteInCommittee(?bool $voteInCommittee): void
    {
        $this->voteInCommittee = $voteInCommittee;
    }

    public function getSubscriptionType(): ?string
    {
        return $this->subscriptionType;
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

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function setCommittee(?Committee $committee): void
    {
        $this->committee = $committee;
    }

    public function getCommitteeUuids(): array
    {
        return $this->committeeUuids;
    }

    public function getCities(): array
    {
        return $this->cities;
    }

    public function toArray(): array
    {
        $roles = [
            'CommitteeSupervisors' => $this->includeCommitteeSupervisors,
            'CommitteeHosts' => $this->includeCommitteeHosts,
            'CitizenProjectHosts' => $this->includeCitizenProjectHosts,
        ];

        return array_merge(
            [
                'gender' => $this->gender,
                'ageMin' => $this->ageMin,
                'ageMax' => $this->ageMax,
                'firstName' => $this->firstName,
                'lastName' => $this->lastName,
                'city' => $this->city,
                'interests' => $this->interests,
                'registeredSince' => $this->registeredSince ? $this->registeredSince->format('Y-m-d') : null,
                'registeredUntil' => $this->registeredUntil ? $this->registeredUntil->format('Y-m-d') : null,
                'referentTags' => 1 === \count($this->referentTags) ? current($this->referentTags)->getId() : null,
                'emailSubscription' => $this->emailSubscription,
                'voteInCommittee' => $this->voteInCommittee,
                'sort' => $this->sort,
                'order' => $this->order,
                'committee' => $this->committee ? $this->committee->getUuidAsString() : null,
                'includeRoles' => array_keys(
                    \array_filter($roles, static function ($role) {
                        return true === $role;
                    })
                ),
                'excludeRoles' => array_keys(
                    \array_filter($roles, static function ($role) {
                        return false === $role;
                    })
                ),
            ],
            array_filter([
                'includeAdherentsNoCommittee' => $this->includeAdherentsNoCommittee,
                'includeAdherentsInCommittee' => $this->includeAdherentsInCommittee,
            ]),
        );
    }
}
