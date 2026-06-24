<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\PublicFeed;

class PublicTimelineSanitizer
{
    private const array ALLOWED_KEYS = [
        'type', 'identifier', 'title', 'description', 'category', 'address', 'post_address',
        'image', 'media', 'url', 'live_url', 'media_type', 'begin_at', 'date',
        'mode', 'cta_label', 'cta_link',
    ];

    private const array ALLOWED_AUTHOR_KEYS = [
        'first_name', 'image_url', 'theme',
    ];

    /**
     * Kept date fields, reduced to the calendar day (no time) before exposure.
     */
    private const array DAY_ONLY_KEYS = ['begin_at', 'date'];

    /**
     * @param array<string, mixed> $display
     *
     * @return array<string, mixed>
     */
    public function sanitize(array $display): array
    {
        $clean = array_intersect_key($display, array_flip(self::ALLOWED_KEYS));

        foreach (self::DAY_ONLY_KEYS as $key) {
            if (isset($clean[$key])) {
                $clean[$key] = $this->toDay($clean[$key]);
            }
        }

        if (isset($display['author']) && \is_array($display['author'])) {
            $clean['author'] = array_intersect_key($display['author'], array_flip(self::ALLOWED_AUTHOR_KEYS));
        }

        return $clean;
    }

    /**
     * Reduces an ISO-8601 timeline date to its calendar day in its own offset
     * (e.g. "2026-06-01T18:00:00+02:00" => "2026-06-01"); a null/empty value stays null.
     */
    private function toDay(mixed $value): ?string
    {
        if (!\is_string($value) || '' === $value) {
            return null;
        }

        return new \DateTimeImmutable($value)->format('Y-m-d');
    }
}
