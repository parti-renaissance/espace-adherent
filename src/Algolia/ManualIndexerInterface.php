<?php

namespace AppBundle\Algolia;

interface ManualIndexerInterface
{
    public function index($entities, array $options = []): void;
}
