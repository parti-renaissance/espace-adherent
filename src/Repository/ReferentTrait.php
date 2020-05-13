<?php

namespace App\Repository;

use App\Entity\Adherent;

trait ReferentTrait
{
    private function checkReferent(Adherent $referent): void
    {
        if (!$referent->isReferent()) {
            throw new \InvalidArgumentException('Adherent must be a referent.');
        }
    }
}
