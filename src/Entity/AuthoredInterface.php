<?php

namespace AppBundle\Entity;

interface AuthoredInterface
{
    public function getAuthor(): ?Adherent;
}
