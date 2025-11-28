<?php

declare(strict_types=1);

namespace App\Entity;

interface AuthoredInterface
{
    public function getAuthor(): ?Adherent;
}
