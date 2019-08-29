<?php

namespace AppBundle\ApplicationRequest\Filter;

use AppBundle\Entity\ApplicationRequest\ApplicationRequestTag;
use AppBundle\Entity\ApplicationRequest\Theme;

class ListFilter
{
    private $firstName;
    private $lastName;
    private $gender;
    private $tag;
    private $isAdherent;
    private $isInMyTeam;
    private $theme;
    private $inseeCodes = [];

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

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getTag(): ?ApplicationRequestTag
    {
        return $this->tag;
    }

    public function setTag(?ApplicationRequestTag $tag): void
    {
        $this->tag = $tag;
    }

    public function isAdherent(): ?bool
    {
        return $this->isAdherent;
    }

    public function setIsAdherent(?bool $isAdherent): void
    {
        $this->isAdherent = $isAdherent;
    }

    public function getIsInMyTeam(): ?int
    {
        return $this->isInMyTeam;
    }

    public function setIsInMyTeam(?int $isInMyTeam): void
    {
        $this->isInMyTeam = $isInMyTeam;
    }

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): void
    {
        $this->theme = $theme;
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
