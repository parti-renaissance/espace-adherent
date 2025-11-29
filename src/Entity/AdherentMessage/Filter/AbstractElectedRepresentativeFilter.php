<?php

declare(strict_types=1);

namespace App\Entity\AdherentMessage\Filter;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
abstract class AbstractElectedRepresentativeFilter extends AbstractAdherentMessageFilter implements CampaignAdherentMessageFilterInterface
{
    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $gender;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255)]
    #[ORM\Column(nullable: true)]
    private $firstName;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255)]
    #[ORM\Column(nullable: true)]
    private $lastName;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $mandate;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $politicalFunction;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $label;

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

    public function setMandate(?string $mandate = null): void
    {
        $this->mandate = $mandate;
    }

    public function getPoliticalFunction(): ?string
    {
        return $this->politicalFunction;
    }

    public function setPoliticalFunction(?string $politicalFunction = null): void
    {
        $this->politicalFunction = $politicalFunction;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label = null): void
    {
        $this->label = $label;
    }
}
