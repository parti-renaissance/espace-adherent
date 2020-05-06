<?php

namespace App\MediaGenerator\Command;

use App\MediaGenerator\ColorUtils;
use Symfony\Component\Validator\Constraints as Assert;

class CitizenProjectImageCommand extends AbstractCitizenProjectMediaCommand
{
    /**
     * @var string
     *
     * @Assert\Length(max=1, maxMessage="citizen_project.emoji.max_length")
     */
    private $emoji;

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

    public function getEmoji(): ?string
    {
        return $this->emoji;
    }

    public function setEmoji(string $emoji): void
    {
        $this->emoji = $emoji;
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
        $this->departmentCode = $departmentCode;
    }

    public function getRgbaColor(float $opacity = 1.0): string
    {
        return ColorUtils::hex2RGBAAsString($this->getBackgroundColor(), $opacity);
    }
}
