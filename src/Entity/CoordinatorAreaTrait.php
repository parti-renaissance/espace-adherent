<?php

namespace AppBundle\Entity;

use AppBundle\Exception\CoordinatorAreaAlreadyTreatedException;

trait CoordinatorAreaTrait
{
    /**
     * @var Adherent|null
     */
    private $creator;

    public function getCreator(): ?Adherent
    {
        return $this->creator;
    }

    public function setCreator(?Adherent $creator): void
    {
        $this->creator = $creator;
    }

    public function preRefused(): void
    {
        if ($this->isApproved() || $this->isRefused()) {
            throw new CoordinatorAreaAlreadyTreatedException($this->uuid);
        }

        $this->status = self::PRE_REFUSED;
    }

    public function preApproved(): void
    {
        if ($this->isApproved() || $this->isRefused()) {
            throw new CoordinatorAreaAlreadyTreatedException($this->uuid);
        }

        $this->status = self::PRE_APPROVED;
    }

    public function isPreApproved(): bool
    {
        return self::PRE_APPROVED === $this->status;
    }

    public function isPreRefused(): bool
    {
        return self::PRE_REFUSED === $this->status;
    }
}
