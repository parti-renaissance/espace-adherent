<?php

namespace App\Entity\AdherentMessage\Filter;

use App\Entity\Committee;
use Doctrine\ORM\Mapping as ORM;

trait BasicUserFiltersTrait
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
    private $includeCommitteeSupervisors;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $includeCommitteeHosts;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $includeCitizenProjectHosts;

    /**
     * @var Committee
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Committee")
     */
    private $committee;

    public function includeAdherentsNoCommittee(): ?bool
    {
        return $this->includeAdherentsNoCommittee;
    }

    public function setIncludeAdherentsNoCommittee(?bool $value): void
    {
        $this->includeAdherentsNoCommittee = $value;
    }

    public function includeAdherentsInCommittee(): ?bool
    {
        return $this->includeAdherentsInCommittee;
    }

    public function setIncludeAdherentsInCommittee(?bool $value): void
    {
        $this->includeAdherentsInCommittee = $value;
    }

    public function includeCommitteeSupervisors(): ?bool
    {
        return $this->includeCommitteeSupervisors;
    }

    public function setIncludeCommitteeSupervisors(?bool $value): void
    {
        $this->includeCommitteeSupervisors = $value;
    }

    public function includeCommitteeHosts(): ?bool
    {
        return $this->includeCommitteeHosts;
    }

    public function setIncludeCommitteeHosts(?bool $value): void
    {
        $this->includeCommitteeHosts = $value;
    }

    public function includeCitizenProjectHosts(): ?bool
    {
        return $this->includeCitizenProjectHosts;
    }

    public function setIncludeCitizenProjectHosts(?bool $value): void
    {
        $this->includeCitizenProjectHosts = $value;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function setCommittee(Committee $committee): void
    {
        $this->committee = $committee;
    }
}
