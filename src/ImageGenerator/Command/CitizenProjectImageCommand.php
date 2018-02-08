<?php

namespace AppBundle\ImageGenerator\Command;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

class CitizenProjectImageCommand implements ImageCommandInterface
{
    /**
     * @var string
     *
     * @Assert\Length(min=5, max=400)
     */
    private $citizenProjectTitle;

    /**
     * @var string
     *
     * @Assert\Length(max=1)
     */
    private $emoji;

    /**
     * @var string
     *
     * @Assert\Regex("/^#[a-f0-9]{6}|[a-f0-9]{3}$/")
     */
    private $backgroundColor;

    /**
     * @var string
     *
     * @Assert\Length(min=2, max=50)
     */
    private $city;

    /**
     * @var string
     *
     * @Assert\Regex("/^[0-9]{2,3}$/")
     */
    private $departmentCode;

    /**
     * @var File
     *
     * @Assert\Image(minWidth=1200)
     */
    private $backgroundImage;

    public function getCitizenProjectTitle(): ?string
    {
        return $this->citizenProjectTitle;
    }

    public function setCitizenProjectTitle(string $citizenProjectTitle): void
    {
        $this->citizenProjectTitle = $citizenProjectTitle;
    }

    public function getEmoji(): ?string
    {
        return $this->emoji;
    }

    public function setEmoji(string $emoji): void
    {
        $this->emoji = $emoji;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(string $backgroundColor): void
    {
        $this->backgroundColor = $backgroundColor;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getDepartmentCode(): ?string
    {
        return $this->departmentCode;
    }

    public function setDepartmentCode(string $departmentCode): void
    {
        $this->departmentCode = \str_pad($departmentCode, 2, 0, \STR_PAD_LEFT);
    }

    public function getBackgroundImage(): ?File
    {
        return $this->backgroundImage;
    }

    public function setBackgroundImage(File $backgroundImage): void
    {
        $this->backgroundImage = $backgroundImage;
    }

    public function getImagePath(): string
    {
        if ($this->backgroundImage) {
            return $this->backgroundImage->getPathname();
        }

        throw new \RuntimeException('Background image cannot be empty');
    }
}
