<?php

namespace App\JeMengage\Timeline\FeedProcessor;

class NullProcessor extends AbstractFeedProcessor
{
    public static function getDefaultPriority(): int
    {
        return -100;
    }

    public function process(array $item, array &$context): array
    {
        return $item;
    }

    public function supports(array $item): bool
    {
        return true;
    }
}
