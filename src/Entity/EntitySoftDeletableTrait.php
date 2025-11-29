<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait EntitySoftDeletableTrait
{
    /**
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $deletedAt;

    public function getDeletedAt(): ?\DateTime
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
