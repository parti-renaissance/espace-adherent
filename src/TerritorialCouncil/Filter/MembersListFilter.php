<?php

namespace App\TerritorialCouncil\Filter;

use App\Entity\ReferentTag;
use Symfony\Component\Validator\Constraints as Assert;

class MembersListFilter
{
    /**
     * @var ReferentTag[]
     *
     * @Assert\NotNull
     */
    private $referentTags = [];

    /**
     * @var bool|null
     */
    private $emailSubscription;

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
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices={"lastName"})
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
            'sort' => $this->sort,
            'order' => $this->order,
        ];
    }
}
