<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait EntityAdministratorBlameableTrait
{
    /**
     * @var Administrator|null
     */
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    private $createdByAdministrator;

    /**
     * @var Administrator|null
     */
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    private $updatedByAdministrator;

    public function getCreatedByAdministrator(): ?Administrator
    {
        return $this->createdByAdministrator;
    }

    public function setCreatedByAdministrator(?Administrator $createdByAdministrator): void
    {
        $this->createdByAdministrator = $createdByAdministrator;
    }

    public function getUpdatedByAdministrator(): ?Administrator
    {
        return $this->updatedByAdministrator;
    }

    public function setUpdatedByAdministrator(?Administrator $updatedByAdministrator): void
    {
        $this->updatedByAdministrator = $updatedByAdministrator;
    }
}
