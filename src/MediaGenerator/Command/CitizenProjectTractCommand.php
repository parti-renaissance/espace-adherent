<?php

namespace App\MediaGenerator\Command;

use App\MediaGenerator\ColorUtils;
use Symfony\Component\Validator\Constraints as Assert;

class CitizenProjectTractCommand extends AbstractCitizenProjectMediaCommand
{
    /**
     * @var string
     *
     * @Assert\Length(min=5)
     */
    private $description;

    /**
     * @var string
     */
    private $details;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(string $details): void
    {
        $this->details = $details;
    }

    public function getRgbaStartColor(): string
    {
        return ColorUtils::hex2RGBAAsString($this->getBackgroundColor(), 0.0);
    }

    public function getRgbaEndColor(): string
    {
        return ColorUtils::hex2RGBAAsString($this->getBackgroundColor(), 1.0);
    }
}
