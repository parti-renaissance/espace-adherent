<?php

namespace Tests\App\Test\Algolia;

use Algolia\AlgoliaSearchBundle\Indexer\ManualIndexer;

class DummyManualIndexer extends ManualIndexer
{
    public function reIndex($entityName, array $options = [])
    {
        $options = array_merge($options, ['safe' => false]);

        return parent::reIndex($entityName, $options);
    }
}
