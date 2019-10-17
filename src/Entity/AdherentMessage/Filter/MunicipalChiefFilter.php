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
     */
    private $gender;

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
     * @var string
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\NotBlank
     */
    private $inseeCode;

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

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $contactAdherents = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $contactNewsletter = false;

    /**
     * @var string|null
     *
     * @ORM\Column(length=10, nullable=true)
     *
     * @Assert\Length(min=5, max=5)
     */
    private $postalCode;

    public function __construct(string $inseeCode)
    {
        $this->inseeCode = $inseeCode;
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

    public function getInseeCode(): string
    {
        return $this->inseeCode;
    }

    public function setInseeCode(string $inseeCode): void
    {
        $this->inseeCode = $inseeCode;
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

    public function getContactAdherents(): bool
    {
        return $this->contactAdherents;
    }

    public function setContactAdherents(bool $contactAdherents): void
    {
        $this->contactAdherents = $contactAdherents;
    }

    public function getContactNewsletter(): bool
    {
        return $this->contactNewsletter;
    }

    public function setContactNewsletter(bool $contactNewsletter): void
    {
        $this->contactNewsletter = $contactNewsletter;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @Assert\IsTrue(message="adherent_message.filter.municipal_chief.empty_contact_type")
     */
    public function isValid(): bool
    {
        return $this->getContactRunningMateTeam()
            || $this->getContactVolunteerTeam()
            || $this->getContactOnlyRunningMates()
            || $this->getContactOnlyVolunteers()
            || $this->getContactAdherents()
            || $this->getContactNewsletter()
        ;
    }
}
