<?php

namespace App\Entity;

interface AlgoliaIndexedEntityInterface
{
    public function getIndexOptions(): array;
}
