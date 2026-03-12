<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait EntitySoftDeletableTrait
{
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function isDeleted(): bool
    {
        return isset($this->deletedAt);
    }

    public function isNotDeleted(): bool
    {
        return !$this->isDeleted();
    }

    public function recover(): void
    {
        $this->deletedAt = null;
    }
}
