<?php

namespace App\JeMengage\Timeline\FeedProcessor;

abstract class AbstractFeedProcessor implements FeedProcessorInterface
{
    public static function getDefaultPriority(): int
    {
        return 0;
    }
}
