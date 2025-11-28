<?php

declare(strict_types=1);

namespace App\Entity;

interface IndexableEntityInterface extends AlgoliaIndexedEntityInterface
{
    public function isIndexable(): bool;
}
