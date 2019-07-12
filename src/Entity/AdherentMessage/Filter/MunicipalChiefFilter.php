<?php

namespace AppBundle\Entity\AdherentMessage\Filter;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class MunicipalChiefFilter extends AbstractAdherentMessageFilter
{
    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     */
    private $firstName;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     */
    private $lastName;

    /**
     * @var string[]
     *
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @Assert\NotBlank
     */
    private $cities;

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

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $contactVolunteerTeam = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $contactRunningMateTeam = false;

    public function __construct(array $cities)
    {
        $this->cities = $cities;
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

    public function getCities(): array
    {
        return $this->cities;
    }

    public function setCities(array $cities): void
    {
        $this->cities = $cities;
    }

    public function getContactVolunteerTeam(): bool
    {
        return $this->contactVolunteerTeam;
    }

    public function setContactVolunteerTeam(bool $contactVolunteerTeam): void
    {
        $this->contactVolunteerTeam = $contactVolunteerTeam;
    }

    public function getContactRunningMateTeam(): bool
    {
        return $this->contactRunningMateTeam;
    }

    public function setContactRunningMateTeam(bool $contactRunningMateTeam): void
    {
        $this->contactRunningMateTeam = $contactRunningMateTeam;
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
