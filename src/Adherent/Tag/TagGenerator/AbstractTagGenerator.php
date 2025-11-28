<?php

declare(strict_types=1);

namespace App\Adherent\Tag\TagGenerator;

abstract class AbstractTagGenerator implements TagGeneratorInterface
{
    public static function getDefaultPriority(): int
    {
        return 0;
    }
}
