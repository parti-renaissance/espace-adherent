<?php

namespace App\TerritorialCouncil\Filter;

use App\Entity\Committee;
use App\Entity\ElectedRepresentative\Zone;
use App\Entity\ReferentTag;
use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use Symfony\Component\Validator\Constraints as Assert;

class MembersListFilter
{
    /**
     * @var ReferentTag[]
     *
     * @Assert\NotNull
     */
    private $referentTags;

    /**
     * @var string|null
     */
    private $subscriptionType;

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
     * @var int|null
     */
    private $ageMin;

    /**
     * @var int|null
     */
    private $ageMax;

    /**
     * @var array
     */
    private $qualities = [];

    /**
     * @var array
     */
    private $cities = [];

    /**
     * @var array
     */
    private $committees = [];

    /**
     * @var bool|null
     */
    private $emailSubscription;

    /**
     * @var bool|null
     */
    private $isPoliticalCommitteeMember;

    /**
     * @var TerritorialCouncil|null
     */
    private $territorialCouncil;

    /**
     * @var PoliticalCommittee|null
     */
    private $politicalCommittee;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    private $sort = 'adherent.lastName';

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices={"a", "d"})
     */
    private $order = 'a';

    public function __construct(array $referentTags, string $subscriptionType)
    {
        $this->referentTags = $referentTags;
        $this->subscriptionType = $subscriptionType;
    }

    /**
     * @return ReferentTag[]
     */
    public function getReferentTags(): array
    {
        return $this->referentTags;
    }

    public function setReferentTags(array $referentTags): void
    {
        $this->referentTags = $referentTags;
    }

    public function getSubscriptionType(): ?string
    {
        return $this->subscriptionType;
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

    public function getQualities(): ?array
    {
        return $this->qualities;
    }

    public function setQualities(?array $qualities): void
    {
        $this->qualities = $qualities;
    }

    public function getCities(): ?array
    {
        return $this->cities;
    }

    public function setCities(?array $cities): void
    {
        $this->cities = $cities;
    }

    public function getCommittees(): ?array
    {
        return $this->committees;
    }

    public function setCommittees(?array $committees): void
    {
        $this->committees = $committees;
    }

    public function getEmailSubscription(): ?bool
    {
        return $this->emailSubscription;
    }

    public function setEmailSubscription(?bool $emailSubscription): void
    {
        $this->emailSubscription = $emailSubscription;
    }

    public function isPoliticalCommitteeMember(): ?bool
    {
        return $this->isPoliticalCommitteeMember;
    }

    public function setIsPoliticalCommitteeMember(?bool $isPoliticalCommitteeMember): void
    {
        $this->isPoliticalCommitteeMember = $isPoliticalCommitteeMember;
    }

    public function getSort(): string
    {
        return $this->sort;
    }

    public function setSort(string $sort): void
    {
        $this->sort = $sort;
    }

    public function getTerritorialCouncil(): ?TerritorialCouncil
    {
        return $this->territorialCouncil;
    }

    public function setTerritorialCouncil(?TerritorialCouncil $territorialCouncil): void
    {
        $this->territorialCouncil = $territorialCouncil;
    }

    public function getPoliticalCommittee(): ?PoliticalCommittee
    {
        return $this->politicalCommittee;
    }

    public function setPoliticalCommittee(?PoliticalCommittee $politicalCommittee): void
    {
        $this->politicalCommittee = $politicalCommittee;
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
            'ageMin' => $this->ageMin,
            'ageMax' => $this->ageMax,
            'qualities' => $this->qualities,
            'cities' => array_map(function (Zone $zone) {
                return $zone->getId();
            }, $this->cities),
            'committees' => array_map(function (Committee $committee) {
                return $committee->getId();
            }, $this->committees),
            'referentTags' => array_map(function (ReferentTag $referentTag) {
                return $referentTag->getId();
            }, $this->referentTags),
            'emailSubscription' => $this->emailSubscription,
            'isPoliticalCommitteeMember' => $this->isPoliticalCommitteeMember,
            'sort' => $this->sort,
            'order' => $this->order,
        ];
    }
}
