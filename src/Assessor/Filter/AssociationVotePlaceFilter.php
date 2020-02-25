<?php

namespace AppBundle\Assessor\Filter;

use AppBundle\Entity\ReferentTag;

class AssociationVotePlaceFilter
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $city;

    /**
     * @var string[]|null
     */
    private $postalCodes;

    /**
     * @var string|null
     */
    private $country;

    /**
     * @var ReferentTag[]
     */
    private $tags = [];

    /**
     * @var array
     */
    private $inseeCodes = [];

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getPostalCodes(): ?array
    {
        return $this->postalCodes;
    }

    public function setPostalCodes(?array $postalCodes): void
    {
        $this->postalCodes = $postalCodes;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function getInseeCodes(): array
    {
        return $this->inseeCodes;
    }

    public function setInseeCodes(array $inseeCodes): void
    {
        $this->inseeCodes = $inseeCodes;
    }
}
