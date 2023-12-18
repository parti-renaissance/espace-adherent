<?php

namespace App\Adhesion\Command;

use App\Entity\Adherent;

class GenerateActivationCodeCommand
{
    public function __construct(
        public readonly Adherent $adherent,
        public readonly bool $force = false,
    ) {
    }
}
