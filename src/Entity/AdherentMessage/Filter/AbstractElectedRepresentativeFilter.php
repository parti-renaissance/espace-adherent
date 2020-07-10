<?php

namespace App\Entity\AdherentMessage\Filter;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
abstract class AbstractElectedRepresentativeFilter extends AbstractAdherentMessageFilter
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
     * @var array|null
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $mandates = [];

    /**
     * @var array|null
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $politicalFunctions = [];

    /**
     * @var array|null
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $labels = [];

    /**
     * @var array|null
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $userListDefinitions = [];

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $isAdherent;

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

    public function getMandates(): ?array
    {
        return $this->mandates;
    }

    public function setMandates(?array $mandates): void
    {
        $this->mandates = $mandates;
    }

    public function getPoliticalFunctions(): ?array
    {
        return $this->politicalFunctions;
    }

    public function setPoliticalFunctions(?array $politicalFunctions): void
    {
        $this->politicalFunctions = $politicalFunctions;
    }

    public function getLabels(): ?array
    {
        return $this->labels;
    }

    public function setLabels(?array $labels): void
    {
        $this->labels = $labels;
    }

    public function getUserListDefinitions(): ?array
    {
        return $this->userListDefinitions;
    }

    public function setUserListDefinitions(?array $userListDefinitions): void
    {
        $this->userListDefinitions = $userListDefinitions;
    }

    public function getIsAdherent(): ?string
    {
        return $this->isAdherent;
    }

    public function setIsAdherent(?string $isAdherent): void
    {
        $this->isAdherent = $isAdherent;
    }
}
