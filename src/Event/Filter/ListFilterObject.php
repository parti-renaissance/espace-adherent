<?php

namespace AppBundle\Event\Filter;

use Symfony\Component\Validator\Constraints as Assert;

class ListFilterObject
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
     */
    private $firstName;

    /**
     * @var string|null
     */
    private $lastName;

    /**
     * @var string|null
     */
    private $city;

    /**
     * @var \DateTimeInterface|null
     */
    private $registeredSince;

    /**
     * @var \DateTimeInterface|null
     */
    private $registeredUntil;

    /**
     * @var \DateTimeInterface|null
     */
    private $joinedSince;

    /**
     * @var \DateTimeInterface|null
     */
    private $joinedUntil;

    /**
     * @var string|null
     *
     * @Assert\Choice(callback="getSortableFields")
     */
    private $sort;

    /**
     * @var string|null
     *
     * @Assert\Choice(choices={"DESC", "ASC"})
     */
    private $order;

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

    public function getRegisteredSince(): ?\DateTimeInterface
    {
        return $this->registeredSince;
    }

    public function setRegisteredSince(?\DateTimeInterface $registeredSince): void
    {
        $this->registeredSince = $registeredSince;
    }

    public function getRegisteredUntil(): ?\DateTimeInterface
    {
        return $this->registeredUntil;
    }

    public function setRegisteredUntil(?\DateTimeInterface $registeredUntil): void
    {
        $this->registeredUntil = $registeredUntil;
    }

    public function getJoinedSince(): ?\DateTimeInterface
    {
        return $this->joinedSince;
    }

    public function setJoinedSince(?\DateTimeInterface $joinedSince): void
    {
        $this->joinedSince = $joinedSince;
    }

    public function getJoinedUntil(): ?\DateTimeInterface
    {
        return $this->joinedUntil;
    }

    public function setJoinedUntil(?\DateTimeInterface $joinedUntil): void
    {
        $this->joinedUntil = $joinedUntil;
    }

    public function getSort(): ?string
    {
        return $this->sort;
    }

    public function setSort(?string $sort): void
    {
        $this->sort = $sort;
    }

    public function getOrder(): ?string
    {
        return $this->order;
    }

    public function setOrder(?string $order): void
    {
        $this->order = $order;
    }

    public static function getSortableFields(): array
    {
        return [
            'joinedAt',
        ];
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), static function ($value) {
            return null !== $value;
        });
    }
}
