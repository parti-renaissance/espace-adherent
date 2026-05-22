<?php

declare(strict_types=1);

namespace Tests\App\Event;

use App\Event\EventCleaner;
use App\Normalizer\DataCleaner;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class EventCleanerTest extends TestCase
{
    private EventCleaner $cleaner;

    protected function setUp(): void
    {
        $this->cleaner = new EventCleaner(new DataCleaner());
    }

    public function testCleanRemovesKeysNotInAllowList(): void
    {
        $cleaned = $this->cleaner->cleanEventData([
            'name' => 'My event',
            'participants_count' => 42,
            'capacity' => 100,
        ]);

        self::assertSame('My event', $cleaned['name']);
        self::assertNull($cleaned['participants_count']);
        self::assertNull($cleaned['capacity']);
    }

    public function testCleanKeepsAllowedScalarKeys(): void
    {
        $cleaned = $this->cleaner->cleanEventData([
            'name' => 'My event',
            'uuid' => 'abc-123',
            'status' => 'SCHEDULED',
            'visibility' => 'public',
        ]);

        self::assertSame('My event', $cleaned['name']);
        self::assertSame('abc-123', $cleaned['uuid']);
        self::assertSame('SCHEDULED', $cleaned['status']);
        self::assertSame('public', $cleaned['visibility']);
    }

    public function testCleanReducesNestedPostAddressToAllowedSubKeys(): void
    {
        $cleaned = $this->cleaner->cleanEventData([
            'post_address' => [
                'address' => '10 rue de la Paix',
                'city_name' => 'Paris',
                'postal_code' => '75002',
                'country' => 'FR',
                'latitude' => 48.86,
                'longitude' => 2.33,
            ],
        ]);

        self::assertSame('Paris', $cleaned['post_address']['city_name']);
        self::assertSame('75002', $cleaned['post_address']['postal_code']);
        self::assertSame('FR', $cleaned['post_address']['country']);
        self::assertNull($cleaned['post_address']['address']);
        self::assertNull($cleaned['post_address']['latitude']);
        self::assertNull($cleaned['post_address']['longitude']);
    }

    public function testCleanReducesNestedOrganizerToAllowedSubKeys(): void
    {
        $cleaned = $this->cleaner->cleanEventData([
            'organizer' => [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'instance' => 'committee',
                'role' => 'animator',
                'zone' => 'Paris',
                'image_url' => 'https://example.test/jane.png',
                'uuid' => 'organizer-uuid',
                'email_address' => 'jane@example.test',
            ],
        ]);

        self::assertSame('Jane', $cleaned['organizer']['first_name']);
        self::assertSame('Doe', $cleaned['organizer']['last_name']);
        self::assertSame('committee', $cleaned['organizer']['instance']);
        self::assertSame('animator', $cleaned['organizer']['role']);
        self::assertSame('Paris', $cleaned['organizer']['zone']);
        self::assertSame('https://example.test/jane.png', $cleaned['organizer']['image_url']);
        self::assertNull($cleaned['organizer']['uuid']);
        self::assertNull($cleaned['organizer']['email_address']);
    }

    public function testCleanTruncatesDatetimeKeysEndingInAtToDateOnly(): void
    {
        $cleaned = $this->cleaner->cleanEventData([
            'begin_at' => '2026-05-22T18:30:00+02:00',
            'user_registered_at' => new \DateTime('2026-05-22 18:30:00'),
        ]);

        self::assertSame('2026-05-22', $cleaned['begin_at']);
        self::assertSame('2026-05-22', $cleaned['user_registered_at']);
    }

    public function testCleanLeavesNullValuesUntouched(): void
    {
        $cleaned = $this->cleaner->cleanEventData([
            'begin_at' => null,
            'name' => null,
        ]);

        self::assertNull($cleaned['begin_at']);
        self::assertNull($cleaned['name']);
    }
}
