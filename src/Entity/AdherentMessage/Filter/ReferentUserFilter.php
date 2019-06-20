<?php

namespace AppBundle\Entity\AdherentMessage\Filter;

use AppBundle\Entity\ReferentTag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @var ReferentTag[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\ReferentTag")
     */
    private $referentTags;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $registeredSince;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $registeredUntil;

    public function __construct(array $referentTags)
    {
        $this->referentTags = new ArrayCollection();

        foreach ($referentTags as $tag) {
            $this->addReferentTag($tag);
        }
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

    /**
     * @return ReferentTag[]
     */
    public function getReferentTags(): array
    {
        return $this->referentTags->toArray();
    }

    public function addReferentTag(ReferentTag $referentTag): void
    {
        if (!$this->referentTags->contains($referentTag)) {
            $this->referentTags->add($referentTag);
        }
    }

    public function removeReferentTag(ReferentTag $referentTag): void
    {
        $this->referentTags->removeElement($referentTag);
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
}
