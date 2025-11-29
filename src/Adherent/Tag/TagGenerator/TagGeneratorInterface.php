<?php

declare(strict_types=1);

namespace App\Adherent\Tag\TagGenerator;

use App\Entity\Adherent;

interface TagGeneratorInterface
{
    public function generate(Adherent $adherent, array $previousTags): array;

    public static function getDefaultPriority(): int;
}
