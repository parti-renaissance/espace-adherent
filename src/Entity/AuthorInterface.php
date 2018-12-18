<?php

namespace AppBundle\Entity;

/**
 * Interface is used for denormalization to get a logged used (ex. AppBundle\Normalizer\AuthorNormalizer).
 */
interface AuthorInterface
{
    public function setAuthor(Adherent $adherent): void;

    public function getAuthor(): Adherent;
}
