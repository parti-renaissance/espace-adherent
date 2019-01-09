<?php

namespace AppBundle\Entity;

/**
 * Interface is used for denormalization to get a logged user (ex. AppBundle\Normalizer\AuthorDenormalizer).
 */
interface AuthorInterface
{
    public function setAuthor(Adherent $adherent): void;

    public function getAuthor(): ?Adherent;
}
