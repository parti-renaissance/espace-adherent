<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Event\EventCategory;
use App\Entity\Geo\Zone;
use Symfony\Component\Validator\Constraints as Assert;

class ListFilter
{
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    private ?EventCategory $category = null;

    private ?Zone $zone = null;

    private ?Zone $defaultZone;

    public function __construct(?Zone $zone = null)
    {
        $this->defaultZone = $zone;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getCategory(): ?EventCategory
    {
        return $this->category;
    }

    public function setCategory(?EventCategory $category): void
    {
        $this->category = $category;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): void
    {
        $this->zone = $zone;
    }

    public function getDefaultZone(): ?Zone
    {
        return $this->defaultZone;
    }

    public function setDefaultZone(?Zone $defaultZone): void
    {
        $this->defaultZone = $defaultZone;
    }
}
