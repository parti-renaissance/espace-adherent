<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Indexer;

/**
 * Typed view over the indexer get_items response. The item order is the indexer ranking authority and
 * must be preserved downstream. An invalid payload (missing items array, or an entry without a usable
 * external_id) throws a domain RuntimeException, mapped to a 503 by the controller.
 */
class FeedResponse
{
    /**
     * @param FeedItem[] $items
     */
    public function __construct(public readonly array $items)
    {
    }

    public static function fromArray(array $payload): self
    {
        if (!\is_array($payload['items'] ?? null)) {
            throw new \RuntimeException('Indexer get_items returned an invalid payload.');
        }

        $items = [];
        foreach ($payload['items'] as $entry) {
            $externalId = \is_array($entry) ? ($entry['external_id'] ?? null) : null;
            if (!\is_string($externalId) || '' === $externalId) {
                throw new \RuntimeException('Indexer get_items returned an item without a valid external_id.');
            }

            $items[] = new FeedItem(
                $externalId,
                \is_string($entry['kind'] ?? null) ? $entry['kind'] : '',
                (float) ($entry['hot_score'] ?? 0.0),
            );
        }

        return new self($items);
    }

    /**
     * @return string[] external_ids in the indexer-returned order
     */
    public function getExternalIds(): array
    {
        return array_map(static function (FeedItem $item): string {
            return $item->externalId;
        }, $this->items);
    }
}
