<?php

namespace AppBundle\Entity;

use AppBundle\Exception\CoordinatorAreaAlreadyTreatedException;
use Doctrine\ORM\Mapping as ORM;

trait CoordinatorAreaTrait
{
    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $coordinatorComment;

    /**
     * @var Adherent|null
     */
    private $creator;

    /**
     * @return string
     */
    public function getCoordinatorComment(): ?string
    {
        return $this->coordinatorComment;
    }

    /**
     * @param string $coordinatorComment
     */
    public function setCoordinatorComment(?string $coordinatorComment): void
    {
        $this->coordinatorComment = $coordinatorComment;
    }

    /**
     * @return Adherent|null
     */
    public function getCreator(): ?Adherent
    {
        return $this->creator;
    }

    /**
     * @param Adherent|null $creator
     */
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
