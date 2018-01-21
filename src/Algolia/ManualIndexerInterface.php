<?php

namespace AppBundle\Algolia;

interface ManualIndexerInterface
{
    public function index($entities, array $options = []): void;

    public function unIndex($entities, array $options = []): void;

    public function reIndex($entityName, array $options = []): int;
}
