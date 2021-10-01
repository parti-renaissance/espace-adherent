<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait EntityAdministratorBlameableTrait
{
    /**
     * @var Administrator|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Administrator")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $createdByAdministrator;

    /**
     * @var Administrator|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Administrator")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
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
