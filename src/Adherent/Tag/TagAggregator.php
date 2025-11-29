<?php

declare(strict_types=1);

namespace App\Adherent\Tag;

use App\Adherent\Tag\TagGenerator\TagGeneratorInterface;
use App\Entity\Adherent;

class TagAggregator
{
    /**
     * @param iterable|TagGeneratorInterface[] $generators
     */
    public function __construct(private readonly iterable $generators)
    {
    }

    public function getTags(Adherent $adherent): array
    {
        $tags = [];

        foreach ($this->generators as $generator) {
            $tags = array_merge($tags, $generator->generate($adherent, $tags));
        }

        return array_values(array_unique($tags));
    }
}
