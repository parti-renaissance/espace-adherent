<?php

declare(strict_types=1);

namespace App\ManagedUsers;

use App\Adherent\MandateTypeEnum;
use App\Adherent\Tag\TagEnum;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
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
    #[Groups(['filter_write'])]
    public array $agoraUuids;

    /**
     * @var string[]
     */
    public array $cities;

    public ?bool $voteInCommittee = null;

    #[Groups(['filter_write'])]
    public ?bool $isCertified = null;

    #[Groups(['filter_write'])]
    public ?\DateTime $firstMembershipSince = null;

    #[Groups(['filter_write'])]
    public ?\DateTime $firstMembershipBefore = null;

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
        array $agoraUuids = [],
        array $cities = [],
        array $zones = [],
    ) {
        if (empty($managedZones) && empty($zones) && empty($committeeUuids) && empty($agoraUuids)) {
            throw new \InvalidArgumentException('ManagedUser filter should have managed zones or selected zones or committee or agora');
        }

        $this->subscriptionType = $subscriptionType;
        $this->managedZones = $managedZones;
        $this->zones = $zones;
        $this->committeeUuids = $committeeUuids;
        $this->agoraUuids = $agoraUuids;
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

    #[Groups(['filter_write'])]
    public function setFirstMembership(array $startEnd): void
    {
        if (!empty($startEnd['start'])) {
            $this->firstMembershipSince = new \DateTime($startEnd['start']);
        }

        if (!empty($startEnd['end'])) {
            $this->firstMembershipBefore = new \DateTime($startEnd['end']);
        }
    }
}
