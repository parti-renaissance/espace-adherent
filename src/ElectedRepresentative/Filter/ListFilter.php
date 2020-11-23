<?php

namespace App\ElectedRepresentative\Filter;

use App\Entity\ElectedRepresentative\ElectedRepresentativeTypeEnum;
use App\Entity\Geo\Zone;
use App\Entity\UserListDefinition;
use Symfony\Component\Validator\Constraints as Assert;

class ListFilter
{
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
     */
    private $gender;

    /**
     * @var array|null
     */
    private $labels = [];

    /**
     * @var array|null
     */
    private $mandates = [];

    /**
     * @var array|null
     */
    private $politicalFunctions = [];

    /**
     * @var array|null
     */
    private $cities = [];

    /**
     * @var array|null
     */
    private $userListDefinitions = [];

    /**
     * @var string|null
     *
     * @Assert\Choice(choices=ElectedRepresentativeTypeEnum::ALL, strict=true)
     */
    private $contactType;

    /**
     * @var bool|null
     */
    private $emailSubscribed;

    /**
     * @var Zone[]
     */
    private $managedZones = [];

    /**
     * @var Zone[]
     *
     * @Assert\NotNull
     */
    private $zones = [];

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices={"lastName"})
     */
    private $sort = 'lastName';

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices={"a", "d"})
     */
    private $order = 'a';

    public function __construct(array $managedZones = [])
    {
        $this->managedZones = $managedZones;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
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

    public function getLabels(): ?array
    {
        return $this->labels;
    }

    public function setLabels(?array $labels): void
    {
        $this->labels = $labels;
    }

    public function getMandates(): ?array
    {
        return $this->mandates;
    }

    public function setMandates(?array $mandates): void
    {
        $this->mandates = $mandates;
    }

    public function getPoliticalFunctions(): ?array
    {
        return $this->politicalFunctions;
    }

    public function setPoliticalFunctions(?array $politicalFunctions): void
    {
        $this->politicalFunctions = $politicalFunctions;
    }

    public function getUserListDefinitions(): ?array
    {
        return $this->userListDefinitions;
    }

    public function setUserListDefinitions(?array $userListDefinitions): void
    {
        $this->userListDefinitions = $userListDefinitions;
    }

    public function getContactType(): ?string
    {
        return $this->contactType;
    }

    public function setContactType(string $contactType = null): void
    {
        $this->contactType = $contactType;
    }

    public function isEmailSubscribed(): ?bool
    {
        return $this->emailSubscribed;
    }

    public function setEmailSubscribed(?bool $emailSubscribed = null): void
    {
        $this->emailSubscribed = $emailSubscribed;
    }

    /**
     * @return Zone[]
     */
    public function getManagedZones(): array
    {
        return $this->managedZones;
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
        foreach ($this->zones as $key => $tag) {
            if ($tag->getId() === $zone->getId()) {
                unset($this->zones[$key]);
            }
        }
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

    public function toArray(): array
    {
        return [
            'gender' => $this->gender,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'cities' => array_values($this->cities),
            'labels' => $this->labels,
            'mandates' => $this->mandates,
            'politicalFunctions' => $this->politicalFunctions,
            'userListDefinitions' => array_map(function (UserListDefinition $label) {
                return $label->getId();
            }, $this->userListDefinitions),
            'zones' => array_map(static function (Zone $zone) {
                return $zone->getId();
            }, $this->zones),
            'sort' => $this->sort,
            'order' => $this->order,
            'contactType' => $this->contactType,
            'emailSubscribed' => $this->emailSubscribed,
        ];
    }
}
