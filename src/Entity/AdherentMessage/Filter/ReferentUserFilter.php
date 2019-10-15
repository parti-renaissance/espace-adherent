<?php

namespace AppBundle\Entity\AdherentMessage\Filter;

use AppBundle\Entity\ReferentTag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class ReferentUserFilter extends AbstractUserFilter
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
     * @var ReferentTag[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\ReferentTag")
     *
     * @Assert\NotNull
     */
    private $referentTags;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $contactOnlyVolunteers = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $contactOnlyRunningMates = false;

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

    public function getContactOnlyVolunteers(): bool
    {
        return $this->contactOnlyVolunteers;
    }

    public function setContactOnlyVolunteers(bool $contactOnlyVolunteers): void
    {
        $this->contactOnlyVolunteers = $contactOnlyVolunteers;
    }

    public function getContactOnlyRunningMates(): bool
    {
        return $this->contactOnlyRunningMates;
    }

    public function setContactOnlyRunningMates(bool $contactOnlyRunningMates): void
    {
        $this->contactOnlyRunningMates = $contactOnlyRunningMates;
    }
}
