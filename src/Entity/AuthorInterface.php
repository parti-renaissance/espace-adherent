<?php

namespace App\Entity;

/**
 * Interface is used for denormalization to get a logged user (ex. App\Normalizer\AuthorDenormalizer).
 */
interface AuthorInterface
{
    public function setAuthor(Adherent $adherent): void;

    public function getAuthor(): ?Adherent;
}
