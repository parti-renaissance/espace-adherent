<?php

declare(strict_types=1);

namespace App\Entity;

use App\Normalizer\AuthorDenormalizer;

/**
 * Interface is used for denormalization to get a logged user
 *
 * @see AuthorDenormalizer
 */
interface AuthorInterface
{
    public function setAuthor(?Adherent $adherent): void;

    public function getAuthor(): ?Adherent;
}
