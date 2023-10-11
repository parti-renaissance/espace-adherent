<?php

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
            $tags[] = $generator->generate($adherent);
        }

        return array_values(array_filter($tags));
    }
}
