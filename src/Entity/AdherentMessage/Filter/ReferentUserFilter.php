<?php

namespace AppBundle\Entity\AdherentMessage\Filter;

use AppBundle\Entity\ReferentTag;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ReferentUserFilter extends AbstractAdherentMessageFilter
{
    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $includeAdherentsNoCommittee = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $includeAdherentsInCommittee = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $includeCommitteeSupervisors = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $includeCommitteeHosts = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $includeCitizenProjectHosts = true;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $gender;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ageMin;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ageMax;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $firstName;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $lastName;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $city;

    /**
     * @var array|null
     *
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $interests = [];

    /**
     * @var ReferentTag
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ReferentTag")
     */
    private $referentTag;

    public function __construct(ReferentTag $referentTag)
    {
        $this->referentTag = $referentTag;
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

    public function includeCommitteeSupervisors(): bool
    {
        return $this->includeCommitteeSupervisors;
    }

    public function setIncludeCommitteeSupervisors(bool $value): void
    {
        $this->includeCommitteeSupervisors = $value;
    }

    public function includeCommitteeHosts(): bool
    {
        return $this->includeCommitteeHosts;
    }

    public function setIncludeCommitteeHosts(bool $value): void
    {
        $this->includeCommitteeHosts = $value;
    }

    public function includeCitizenProjectHosts(): bool
    {
        return $this->includeCitizenProjectHosts;
    }

    public function setIncludeCitizenProjectHosts(bool $value): void
    {
        $this->includeCitizenProjectHosts = $value;
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

    public function getReferentTag(): ReferentTag
    {
        return $this->referentTag;
    }

    public function setReferentTag(ReferentTag $referentTag): void
    {
        $this->referentTag = $referentTag;
    }
}
