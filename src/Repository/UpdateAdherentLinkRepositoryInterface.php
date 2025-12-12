<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Adherent;

interface UpdateAdherentLinkRepositoryInterface
{
    public function updateLinksWithNewAdherent(Adherent $adherent): void;

    public function updateAdherentLink(object $object): void;
}
