<?php

namespace App\MediaGenerator\Command;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractCitizenProjectMediaCommand implements MediaCommandInterface
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
     * @Assert\Regex("/^#[a-f0-9]{6}|[a-f0-9]{3}$/")
     */
    private $backgroundColor;

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

    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(string $backgroundColor): void
    {
        $this->backgroundColor = $backgroundColor;
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
        if ($this->getBackgroundImage()) {
            return $this->getBackgroundImage()->getPathname();
        }

        throw new \RuntimeException('Background image cannot be empty');
    }
}
