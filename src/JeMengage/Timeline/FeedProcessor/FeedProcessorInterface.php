<?php

namespace App\JeMengage\Timeline\FeedProcessor;

interface FeedProcessorInterface
{
    public function process(array $item, array &$context): array;

    public function supports(array $item): bool;
}
