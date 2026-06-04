<?php

declare(strict_types=1);

namespace App\Algolia;

use App\Entity\AlgoliaIndexedEntityInterface;

/**
 * Single choke point for feeding the Algolia search index.
 *
 * Depended upon (not the concrete manager) so the indexing path can be decorated — e.g. to
 * additionally mirror documents to a local table — without the consumers knowing.
 */
interface AlgoliaIndexerInterface
{
    public function postPersist(AlgoliaIndexedEntityInterface $entity): void;

    public function postUpdate(AlgoliaIndexedEntityInterface $entity): void;

    public function preRemove(AlgoliaIndexedEntityInterface $entity): void;

    /**
     * @param AlgoliaIndexedEntityInterface[] $entities
     */
    public function batch(array $entities): void;
}
