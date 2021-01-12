<?php

namespace App\ManagedUsers;

use App\Entity\Committee;
use App\Entity\Geo\Zone;
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
     * @var bool|null
     */
    private $isCommitteeMember;

    /**
     * @var bool
     */
    private $includeCommitteeSupervisors;

    /**
     * @var bool
     */
    private $includeCommitteeProvisionalSupervisors;

    /**
     * @var bool
     */
    private $includeCommitteeHosts;

    /**
     * @var bool
     */
    private $includeCitizenProjectHosts;

    /**
     * @var Zone[]
     *
     * @Assert\Expression(
     *     expression="this.getManagedZones() or this.getZones()",
     *     message="referent.managed_zone.empty"
     * )
     */
    private $managedZones;

    /**
     * @var Zone[]
     */
    private $zones;

    /**
     * @var bool|null
     */
    private $emailSubscription;

    /**
     * @var bool|null
     */
    private $smsSubscription;

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

    /**
     * @var bool|null
     */
    private $isCertified;

    public function __construct(
        string $subscriptionType = null,
        array $managedZones = [],
        array $committeeUuids = [],
        array $cities = [],
        array $zones = []
    ) {
        if (empty($managedZones) && empty($zones)) {
            throw new \InvalidArgumentException('Both managed zones and zones could not be empty');
        }

        $this->subscriptionType = $subscriptionType;
        $this->managedZones = $managedZones;
        $this->zones = $zones;
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

    public function isCommitteeMember(): ?bool
    {
        return $this->isCommitteeMember;
    }

    public function setIsCommitteeMember(?bool $value): void
    {
        $this->isCommitteeMember = $value;
    }

    public function includeCommitteeSupervisors(): ?bool
    {
        return $this->includeCommitteeSupervisors;
    }

    public function setIncludeCommitteeSupervisors(?bool $value): void
    {
        $this->includeCommitteeSupervisors = $value;
    }

    public function includeCommitteeProvisionalSupervisors(): ?bool
    {
        return $this->includeCommitteeProvisionalSupervisors;
    }

    public function setIncludeCommitteeProvisionalSupervisors(?bool $value): void
    {
        $this->includeCommitteeProvisionalSupervisors = $value;
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
     * @return Zones[]
     */
    public function getManagedZones(): array
    {
        return $this->managedZones;
    }

    public function addManagedZone(Zone $zone): void
    {
        $this->managedZones[] = $zone;
    }

    public function removeManagedZone(Zone $zone): void
    {
        foreach ($this->managedZones as $key => $managedZone) {
            if ($managedZone->getId() === $zone->getId()) {
                unset($this->managedZones[$key]);
            }
        }

        $this->managedZones = array_values($this->managedZones);
    }

    /**
     * @return Zone[]
     */
    public function getZones(): array
    {
        return $this->zones;
    }

    public function addZone(Zone $zone): void
    {
        $this->zones[] = $zone;
    }

    public function removeZone(Zone $zone): void
    {
        foreach ($this->zones as $key => $value) {
            if ($value->getId() === $zone->getId()) {
                unset($this->zones[$key]);
            }
        }

        $this->zones = array_values($this->zones);
    }

    public function getEmailSubscription(): ?bool
    {
        return $this->emailSubscription;
    }

    public function setEmailSubscription(?bool $emailSubscription): void
    {
        $this->emailSubscription = $emailSubscription;
    }

    public function getSmsSubscription(): ?bool
    {
        return $this->smsSubscription;
    }

    public function setSmsSubscription(?bool $smsSubscription): void
    {
        $this->smsSubscription = $smsSubscription;
    }

    public function getVoteInCommittee(): ?bool
    {
        return $this->voteInCommittee;
    }

    public function setVoteInCommittee(?bool $voteInCommittee): void
    {
        $this->voteInCommittee = $voteInCommittee;
    }

    public function getIsCertified(): ?bool
    {
        return $this->isCertified;
    }

    public function setIsCertified(?bool $isCertified): void
    {
        $this->isCertified = $isCertified;
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
            'CommitteeProvisionalSupervisors' => $this->includeCommitteeProvisionalSupervisors,
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
                'zones' => array_map(static function (Zone $zone) {
                    return $zone->getId();
                }, $this->zones),
                'managedZones' => 1 === \count($this->managedZones) ? current($this->managedZones)->getId() : null,
                'smsSubscription' => $this->smsSubscription,
                'emailSubscription' => $this->emailSubscription,
                'voteInCommittee' => $this->voteInCommittee,
                'isCommitteeMember' => $this->isCommitteeMember,
                'isCertified' => $this->isCertified,
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
        );
    }
}
