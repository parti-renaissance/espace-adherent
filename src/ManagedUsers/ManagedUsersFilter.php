<?php

namespace App\ManagedUsers;

use App\Adherent\MandateTypeEnum;
use App\Adherent\Tag\TagEnum;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
use App\Renaissance\Membership\RenaissanceMembershipFilterEnum;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Provides a way to handle the search parameters.
 */
class ManagedUsersFilter
{
    #[Groups(['filter_write'])]
    public ?string $searchTerm = null;

    #[Groups(['filter_write'])]
    public ?string $gender = null;

    #[Groups(['filter_write'])]
    public ?int $ageMin = null;

    #[Groups(['filter_write'])]
    public ?int $ageMax = null;

    #[Assert\Length(max: 255)]
    #[Groups(['filter_write'])]
    public ?string $firstName = null;

    #[Assert\Length(max: 255)]
    #[Groups(['filter_write'])]
    public ?string $lastName = null;

    #[Assert\Length(max: 255)]
    public ?string $city = null;

    #[Groups(['filter_write'])]
    public array $interests = [];

    #[Groups(['filter_write'])]
    public ?\DateTime $registeredSince = null;

    #[Groups(['filter_write'])]
    public ?\DateTime $registeredUntil = null;

    #[Groups(['filter_write'])]
    public ?bool $isCommitteeMember = null;

    public ?bool $includeCommitteeSupervisors = null;

    public ?bool $includeCommitteeProvisionalSupervisors = null;

    public ?bool $includeCommitteeHosts = null;

    #[Assert\Choice(callback: [TagEnum::class, 'getAdherentTags'])]
    #[Groups(['filter_write'])]
    public ?string $adherentTags = null;

    #[Assert\Choice(callback: [TagEnum::class, 'getElectTags'])]
    #[Groups(['filter_write'])]
    public ?string $electTags = null;

    #[Groups(['filter_write'])]
    public ?string $staticTags = null;

    #[Assert\Choice(choices: MandateTypeEnum::ALL, multiple: true)]
    #[Groups(['filter_write'])]
    public array $mandates = [];

    #[Assert\Choice(choices: MandateTypeEnum::ALL, multiple: true)]
    #[Groups(['filter_write'])]
    public array $declaredMandates = [];

    /**
     * @var Zone[]
     */
    #[Assert\Expression(expression: 'this.getManagedZones() or this.getZones()', message: 'referent.managed_zone.empty')]
    public array $managedZones;

    /**
     * @var Zone[]
     */
    #[Groups(['filter_write'])]
    public array $zones;

    #[Groups(['filter_write'])]
    public ?bool $emailSubscription = null;

    #[Groups(['filter_write'])]
    public ?bool $smsSubscription = null;

    public ?string $subscriptionType;

    #[Assert\Choice(choices: ['createdAt', 'lastName'])]
    #[Assert\NotBlank]
    public string $sort = 'createdAt';

    #[Assert\Choice(choices: ['a', 'd'])]
    #[Assert\NotBlank]
    public string $order = 'd';

    public ?Committee $committee = null;

    /**
     * @var string[]
     */
    #[Groups(['filter_write'])]
    public array $committeeUuids;

    /**
     * @var string[]
     */
    public array $cities;

    public ?bool $voteInCommittee = null;

    #[Groups(['filter_write'])]
    public ?bool $isCertified = null;

    #[Assert\Choice(choices: RenaissanceMembershipFilterEnum::CHOICES)]
    #[Groups(['filter_write'])]
    public ?string $renaissanceMembership = null;

    #[Groups(['filter_write'])]
    public ?\DateTime $lastMembershipSince = null;

    #[Groups(['filter_write'])]
    public ?\DateTime $lastMembershipBefore = null;

    #[Groups(['filter_write'])]
    public ?bool $onlyJeMengageUsers = null;

    #[Groups(['filter_write'])]
    public ?bool $isNewRenaissanceUser = null;

    #[Groups(['filter_write'])]
    public ?bool $isCampusRegistered = null;

    public function __construct(
        ?string $subscriptionType = null,
        array $managedZones = [],
        array $committeeUuids = [],
        array $cities = [],
        array $zones = [],
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

    #[Groups(['filter_write'])]
    public function setAge(array $minMax): void
    {
        if (!empty($minMax['min'])) {
            $this->ageMin = $minMax['min'];
        }

        if (!empty($minMax['max'])) {
            $this->ageMax = $minMax['max'];
        }
    }

    public function getCityAsArray(): array
    {
        return $this->city ? array_map('trim', explode(',', $this->city)) : [];
    }

    #[Groups(['filter_write'])]
    public function setRegistered(array $startEnd): void
    {
        if (!empty($startEnd['start'])) {
            $this->registeredSince = new \DateTime($startEnd['start']);
        }

        if (!empty($startEnd['end'])) {
            $this->registeredUntil = new \DateTime($startEnd['end']);
        }
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

    #[Groups(['filter_write'])]
    public function setLastMembership(array $startEnd): void
    {
        if (!empty($startEnd['start'])) {
            $this->lastMembershipSince = new \DateTime($startEnd['start']);
        }

        if (!empty($startEnd['end'])) {
            $this->lastMembershipBefore = new \DateTime($startEnd['end']);
        }
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
                'registeredSince' => $this->registeredSince?->format('Y-m-d'),
                'registeredUntil' => $this->registeredUntil?->format('Y-m-d'),
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
                'committee' => $this->committee?->getUuidAsString(),
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
}
