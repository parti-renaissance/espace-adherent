<?php

namespace App\ManagedUsers;

use App\Entity\Committee;
use App\Entity\Geo\Zone;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Provides a way to handle the search parameters.
 */
class ManagedUsersFilter
{
    /**
     * @Groups({"filter_write"})
     */
    private ?string $gender = null;

    /**
     * @Groups({"filter_write"})
     */
    private ?int $ageMin = null;

    /**
     * @Groups({"filter_write"})
     */
    private ?int $ageMax = null;

    /**
     * @Assert\Length(max=255)
     *
     * @Groups({"filter_write"})
     */
    private ?string $firstName = null;

    /**
     * @Assert\Length(max=255)
     *
     * @Groups({"filter_write"})
     */
    private ?string $lastName = null;

    /**
     * @Assert\Length(max=255)
     */
    private ?string $city = null;

    /**
     * @Groups({"filter_write"})
     */
    private array $interests = [];

    /**
     * @Groups({"filter_write"})
     */
    private ?\DateTime $registeredSince = null;

    /**
     * @Groups({"filter_write"})
     */
    private ?\DateTime $registeredUntil = null;

    /**
     * @Groups({"filter_write"})
     */
    private ?bool $isCommitteeMember = null;

    private ?bool $includeCommitteeSupervisors = null;

    private ?bool $includeCommitteeProvisionalSupervisors = null;

    private ?bool $includeCommitteeHosts = null;

    /**
     * @Groups({"filter_write"})
     *
     * @Assert\Choice(callback={"App\Adherent\Tag\TagEnum", "getAdherentTags"})
     */
    public ?string $adherentTags = null;

    /**
     * @Groups({"filter_write"})
     *
     * @Assert\Choice(callback={"App\Adherent\Tag\TagEnum", "getElectTags"})
     */
    public ?string $electTags = null;

    /**
     * @Groups({"filter_write"})
     */
    public ?string $staticTags = null;

    /**
     * @Groups({"filter_write"})
     *
     * @Assert\Choice(choices=App\Adherent\MandateTypeEnum::ALL, multiple=true)
     */
    private array $mandates = [];

    /**
     * @Groups({"filter_write"})
     *
     * @Assert\Choice(choices=App\Adherent\MandateTypeEnum::ALL, multiple=true)
     */
    private array $declaredMandates = [];

    /**
     * @var Zone[]
     *
     * @Assert\Expression(
     *     expression="this.getManagedZones() or this.getZones()",
     *     message="referent.managed_zone.empty"
     * )
     */
    private array $managedZones;

    /**
     * @var Zone[]
     *
     * @Groups({"filter_write"})
     */
    private array $zones;

    /**
     * @Groups({"filter_write"})
     */
    private ?bool $emailSubscription = null;

    /**
     * @Groups({"filter_write"})
     */
    private ?bool $smsSubscription = null;

    private ?string $subscriptionType;

    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={"createdAt", "lastName"})
     */
    private string $sort = 'createdAt';

    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={"a", "d"})
     */
    private string $order = 'd';

    private ?Committee $committee = null;

    /**
     * @var string[]
     *
     * @Groups({"filter_write"})
     */
    private array $committeeUuids;

    /**
     * @var string[]
     */
    private array $cities;

    private ?bool $voteInCommittee = null;

    /**
     * @Groups({"filter_write"})
     */
    private ?bool $isCertified = null;

    /**
     * @Groups({"filter_write"})
     * @Assert\Choice(choices=App\Renaissance\Membership\RenaissanceMembershipFilterEnum::CHOICES)
     */
    private ?string $renaissanceMembership = null;

    /**
     * @Groups({"filter_write"})
     */
    private ?\DateTime $lastMembershipSince = null;

    /**
     * @Groups({"filter_write"})
     */
    private ?\DateTime $lastMembershipBefore = null;

    /**
     * @Groups({"filter_write"})
     */
    private ?bool $onlyJeMengageUsers = null;

    /**
     * @Groups({"filter_write"})
     */
    private ?bool $isNewRenaissanceUser = null;

    /**
     * @Groups({"filter_write"})
     */
    private ?bool $isCampusRegistered = null;

    public function __construct(
        ?string $subscriptionType = null,
        array $managedZones = [],
        array $committeeUuids = [],
        array $cities = [],
        array $zones = []
    ) {
        if (empty($managedZones) && empty($zones) && empty($committeeUuids)) {
            throw new \InvalidArgumentException('ManagedUser filter should have managed zones or selected zones or committee');
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

    /**
     * @Groups({"filter_write"})
     */
    public function setAge(array $minMax): void
    {
        if (!empty($minMax['min'])) {
            $this->setAgeMin($minMax['min']);
        }

        if (!empty($minMax['max'])) {
            $this->setAgeMax($minMax['max']);
        }
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

    /**
     * @Groups({"filter_write"})
     */
    public function setRegistered(array $startEnd): void
    {
        if (!empty($startEnd['start'])) {
            $this->setRegisteredSince(new \DateTime($startEnd['start']));
        }

        if (!empty($startEnd['end'])) {
            $this->setRegisteredUntil(new \DateTime($startEnd['end']));
        }
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

    /**
     * @return Zone[]
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

    public function getRenaissanceMembership(): ?string
    {
        return $this->renaissanceMembership;
    }

    public function setRenaissanceMembership(?string $renaissanceMembership): void
    {
        $this->renaissanceMembership = $renaissanceMembership;
    }

    public function getLastMembershipSince(): ?\DateTime
    {
        return $this->lastMembershipSince;
    }

    public function setLastMembershipSince(?\DateTime $lastMembershipSince): void
    {
        $this->lastMembershipSince = $lastMembershipSince;
    }

    public function getLastMembershipBefore(): ?\DateTime
    {
        return $this->lastMembershipBefore;
    }

    public function setLastMembershipBefore(?\DateTime $lastMembershipBefore): void
    {
        $this->lastMembershipBefore = $lastMembershipBefore;
    }

    /**
     * @Groups({"filter_write"})
     */
    public function setLastMembership(array $startEnd): void
    {
        if (!empty($startEnd['start'])) {
            $this->setLastMembershipSince(new \DateTime($startEnd['start']));
        }

        if (!empty($startEnd['end'])) {
            $this->setLastMembershipBefore(new \DateTime($startEnd['end']));
        }
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

    public function setCommitteeUuids(array $committeeUuids): void
    {
        $this->committeeUuids = $committeeUuids;
    }

    public function getCities(): array
    {
        return $this->cities;
    }

    public function setCities(array $cities): void
    {
        $this->cities = $cities;
    }

    public function toArray(): array
    {
        $roles = [
            'CommitteeSupervisors' => $this->includeCommitteeSupervisors,
            'CommitteeProvisionalSupervisors' => $this->includeCommitteeProvisionalSupervisors,
            'CommitteeHosts' => $this->includeCommitteeHosts,
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
                    array_filter($roles, static function ($role) {
                        return true === $role;
                    })
                ),
                'excludeRoles' => array_keys(
                    array_filter($roles, static function ($role) {
                        return false === $role;
                    })
                ),
                'mandates' => $this->mandates,
                'declaredMandates' => $this->declaredMandates,
                'isCampusRegistered' => $this->isCampusRegistered,
            ],
        );
    }

    public function getOnlyJeMengageUsers(): ?bool
    {
        return $this->onlyJeMengageUsers;
    }

    public function setOnlyJeMengageUsers(?bool $onlyJeMengageUsers): void
    {
        $this->onlyJeMengageUsers = $onlyJeMengageUsers;
    }

    public function getIsNewRenaissanceUser(): ?bool
    {
        return $this->isNewRenaissanceUser;
    }

    public function setIsNewRenaissanceUser(?bool $isNewRenaissanceUser): void
    {
        $this->isNewRenaissanceUser = $isNewRenaissanceUser;
    }

    public function getMandates(): array
    {
        return $this->mandates;
    }

    public function setMandates(array $mandates): void
    {
        $this->mandates = $mandates;
    }

    public function getDeclaredMandates(): array
    {
        return $this->declaredMandates;
    }

    public function setDeclaredMandates(array $declaredMandates): void
    {
        $this->declaredMandates = $declaredMandates;
    }

    public function getIsCampusRegistered(): ?bool
    {
        return $this->isCampusRegistered;
    }

    public function setIsCampusRegistered(?bool $isCampusRegistered): void
    {
        $this->isCampusRegistered = $isCampusRegistered;
    }
}
