<?php

declare(strict_types=1);

namespace App\Entity;

interface EntityAdministratorBlameableInterface
{
    public function getCreatedByAdministrator(): ?Administrator;

    public function setCreatedByAdministrator(?Administrator $createdByAdministrator): void;

    public function getUpdatedByAdministrator(): ?Administrator;

    public function setUpdatedByAdministrator(?Administrator $updatedByAdministrator): void;
}
