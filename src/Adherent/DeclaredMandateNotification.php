<?php

namespace App\Adherent;

use App\Entity\Adherent;

class DeclaredMandateNotification
{
    public function __construct(
        public readonly Adherent $adherent,
        public readonly array $addedMandates,
        public readonly array $removedMandates,
    ) {
    }
}
