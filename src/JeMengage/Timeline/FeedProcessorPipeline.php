<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline;

use App\Entity\Adherent;
use App\JeMengage\Timeline\FeedProcessor\FeedProcessorInterface;

/**
 * Applies the tagged FeedProcessor chain to a list of timeline hits. Extracted from DataProvider so the
 * same post-processing runs for both the Algolia read (DataProvider) and the canary read
 * (IndexerTimelineProvider) — one chain, one behaviour (DRY).
 */
class FeedProcessorPipeline
{
    public function __construct(private readonly iterable $processors)
    {
    }

    public function process(Adherent $user, array $items): array
    {
        foreach ($items as &$item) {
            foreach ($this->processors as $processor) {
                /** @var FeedProcessorInterface $processor */
                if ($processor->supports($item, $user)) {
                    $item = $processor->process($item, $user);
                }
            }
        }

        return $items;
    }
}
