<?php

namespace App\Entity;

interface IndexableEntityInterface extends AlgoliaIndexedEntityInterface
{
    public function isIndexable(): bool;
}
