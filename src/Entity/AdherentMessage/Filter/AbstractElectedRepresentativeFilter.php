<?php

namespace App\Entity\AdherentMessage\Filter;

use App\Entity\ReferentTag;
use App\Entity\UserListDefinition;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
abstract class AbstractElectedRepresentativeFilter extends AbstractAdherentMessageFilter implements CampaignAdherentMessageFilterInterface
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
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $mandate;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $politicalFunction;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $label;

    /**
     * @var UserListDefinition|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\UserListDefinition")
     */
    private $userListDefinition;

    /**
     * @var ReferentTag|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ReferentTag")
     *
     * @Assert\NotNull
     */
    private $referentTag;

    public function __construct(ReferentTag $referentTag = null)
    {
        parent::__construct();

        $this->referentTag = $referentTag;
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

    public function getMandate(): ?string
    {
        return $this->mandate;
    }

    public function setMandate(string $mandate = null): void
    {
        $this->mandate = $mandate;
    }

    public function getPoliticalFunction(): ?string
    {
        return $this->politicalFunction;
    }

    public function setPoliticalFunction(string $politicalFunction = null): void
    {
        $this->politicalFunction = $politicalFunction;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label = null): void
    {
        $this->label = $label;
    }

    public function getUserListDefinition(): ?UserListDefinition
    {
        return $this->userListDefinition;
    }

    public function setUserListDefinition(?UserListDefinition $userListDefinition): void
    {
        $this->userListDefinition = $userListDefinition;
    }

    public function getReferentTag(): ?ReferentTag
    {
        return $this->referentTag;
    }

    public function setReferentTag(ReferentTag $referentTag = null): void
    {
        $this->referentTag = $referentTag;
    }
}
