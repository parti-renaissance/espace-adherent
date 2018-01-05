<?php

namespace AppBundle\Algolia;

use Algolia\AlgoliaSearchBundle\Indexer\ManualIndexer as BaseManualIndexer;

class ManualIndexer implements ManualIndexerInterface
{
    private $algolia;

    public function __construct(BaseManualIndexer $algolia)
    {
        $this->algolia = $algolia;
    }

    public function index($entities, array $options = []): void
    {
        $this->algolia->index($entities, $options);
    }
}
