<?php

namespace App\Adherent\Tag\TagGenerator;

use App\Entity\Adherent;

interface TagGeneratorInterface
{
    public function generate(Adherent $adherent): ?string;
}
