<?php

namespace App\Entity;

interface AuthoredInterface
{
    public function getAuthor(): ?Adherent;
}
