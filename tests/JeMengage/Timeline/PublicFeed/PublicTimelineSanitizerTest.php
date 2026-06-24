<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\PublicFeed;

use App\JeMengage\Timeline\PublicFeed\PublicTimelineSanitizer;
use PHPUnit\Framework\TestCase;

final class PublicTimelineSanitizerTest extends TestCase
{
    public function testInternalAndTargetingFieldsAreStripped(): void
    {
        $clean = new PublicTimelineSanitizer()->sanitize($this->fullDisplay());

        foreach (['_tags', 'is_national', 'zone_codes', 'adherent_ids', 'visibility', 'committee_uuid', 'agora_uuid', 'access', 'audience'] as $internal) {
            self::assertArrayNotHasKey($internal, $clean);
        }
    }

    public function testRemovedDisplayFieldsAreNotExposed(): void
    {
        $clean = new PublicTimelineSanitizer()->sanitize($this->fullDisplay());

        foreach (['participants_count', 'finish_at', 'time_zone'] as $removed) {
            self::assertArrayNotHasKey($removed, $clean);
        }
    }

    public function testKeptDatesAreReducedToTheDay(): void
    {
        $clean = new PublicTimelineSanitizer()->sanitize($this->fullDisplay());

        // Time and timezone are dropped: only the calendar day (in the original offset) remains.
        self::assertSame('2026-06-01', $clean['begin_at']);
        self::assertSame('2026-05-20', $clean['date']);
    }

    public function testPublicFieldsAreKept(): void
    {
        $clean = new PublicTimelineSanitizer()->sanitize($this->fullDisplay());

        self::assertSame('event', $clean['type']);
        self::assertSame('My title', $clean['title']);
        self::assertSame('https://example.test/x', $clean['url']);
    }

    public function testAuthorIsReducedToFirstNameImageAndTheme(): void
    {
        $clean = new PublicTimelineSanitizer()->sanitize($this->fullDisplay());

        self::assertSame([
            'first_name' => 'Jean',
            'image_url' => 'https://example.test/a.jpg',
            'theme' => null,
        ], $clean['author']);

        foreach (['uuid', 'last_name', 'name', 'role', 'username', 'instance', 'instance_key', 'scope', 'zone'] as $internal) {
            self::assertArrayNotHasKey($internal, $clean['author']);
        }
    }

    public function testDisplayWithoutAuthorHasNoAuthorKey(): void
    {
        $clean = new PublicTimelineSanitizer()->sanitize(['type' => 'social_network_post', 'title' => 'Post']);

        self::assertArrayNotHasKey('author', $clean);
    }

    /**
     * A realistic display record carrying every key AbstractJeMengageTimelineFeedNormalizer emits,
     * including the internal/targeting ones that must never reach an anonymous client.
     *
     * @return array<string, mixed>
     */
    private function fullDisplay(): array
    {
        return [
            '_tags' => ['event'],
            'type' => 'event',
            'identifier' => 'uuid-1',
            'title' => 'My title',
            'description' => 'desc',
            'category' => 'Cat',
            'url' => 'https://example.test/x',
            'begin_at' => '2026-06-01T18:00:00+02:00',
            'finish_at' => '2026-06-01T20:00:00+02:00',
            'date' => '2026-05-20T10:00:00+00:00',
            'time_zone' => 'Europe/Paris',
            'mode' => 'meeting',
            'participants_count' => 12,
            'is_national' => false,
            'zone_codes' => ['city_75056'],
            'adherent_ids' => [1, 2],
            'visibility' => 'public',
            'committee_uuid' => 'c-uuid',
            'agora_uuid' => 'a-uuid',
            'access' => ['author_id' => 7, 'team_owner_id' => null],
            'audience' => ['include' => ['national' => true]],
            'author' => [
                'uuid' => 'author-uuid',
                'first_name' => 'Jean',
                'last_name' => 'Dupont',
                'name' => 'Jean Dupont',
                'username' => 'jdupont',
                'role' => 'animateur',
                'instance' => 'committee',
                'instance_key' => 'committee:x',
                'zone' => 'department:75',
                'scope' => 'animator',
                'image_url' => 'https://example.test/a.jpg',
                'theme' => null,
            ],
        ];
    }
}
