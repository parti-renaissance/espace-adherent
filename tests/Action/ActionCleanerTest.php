<?php

declare(strict_types=1);

namespace Tests\App\Action;

use App\Action\ActionCleaner;
use App\Normalizer\DataCleaner;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class ActionCleanerTest extends TestCase
{
    private ActionCleaner $cleaner;

    protected function setUp(): void
    {
        $this->cleaner = new ActionCleaner(new DataCleaner());
    }

    /**
     * @return array<string, mixed>
     */
    private function rawAction(): array
    {
        return [
            'uuid' => 'action-uuid',
            'type' => 'pap',
            'status' => 'scheduled',
            'editable' => false,
            'user_registered_at' => null,
            'date' => '2026-06-01T18:30:00+02:00',
            'created_at' => '2026-05-01T10:00:00+02:00',
            'updated_at' => '2026-05-02T10:00:00+02:00',
            'participants_count' => 5,
            'first_participants' => [['first_name' => 'Alice'], ['first_name' => 'Bob']],
            'post_address' => [
                'address' => '10 rue de la Paix',
                'city_name' => 'Paris',
                'postal_code' => '75002',
                'country' => 'FR',
            ],
            'author' => ['first_name' => 'Jane', 'last_name' => 'Doe', 'uuid' => 'author-uuid'],
            'author_scope' => 'committee',
            'author_role' => 'animator',
            'author_zone' => 'Paris',
            'image' => ['url' => 'https://example.test/action.png'],
            'image_url' => 'https://example.test/action.png',
        ];
    }

    public function testCleanKeepsUuidTypeStatus(): void
    {
        $cleaned = $this->cleaner->cleanActionData($this->rawAction());

        self::assertSame('action-uuid', $cleaned['uuid']);
        self::assertSame('pap', $cleaned['type']);
        self::assertSame('scheduled', $cleaned['status']);
        self::assertFalse($cleaned['editable']);
    }

    public function testCleanTruncatesDateToDateOnly(): void
    {
        $cleaned = $this->cleaner->cleanActionData($this->rawAction());

        self::assertSame('2026-06-01', $cleaned['date']);
    }

    public function testCleanReducesPostAddressDroppingStreet(): void
    {
        $cleaned = $this->cleaner->cleanActionData($this->rawAction());

        self::assertSame('Paris', $cleaned['post_address']['city_name']);
        self::assertSame('75002', $cleaned['post_address']['postal_code']);
        self::assertSame('FR', $cleaned['post_address']['country']);
        self::assertNull($cleaned['post_address']['address']);
    }

    public function testCleanReducesAuthorToFirstAndLastName(): void
    {
        $cleaned = $this->cleaner->cleanActionData($this->rawAction());

        self::assertSame('Jane', $cleaned['author']['first_name']);
        self::assertSame('Doe', $cleaned['author']['last_name']);
        self::assertNull($cleaned['author']['uuid']);
    }

    public function testCleanNullsFirstParticipants(): void
    {
        $cleaned = $this->cleaner->cleanActionData($this->rawAction());

        self::assertNull($cleaned['first_participants']);
    }

    public function testCleanNullsParticipantsCount(): void
    {
        $cleaned = $this->cleaner->cleanActionData($this->rawAction());

        self::assertNull($cleaned['participants_count']);
    }

    public function testCleanNullsAuthorOrgFields(): void
    {
        $cleaned = $this->cleaner->cleanActionData($this->rawAction());

        self::assertNull($cleaned['author_scope']);
        self::assertNull($cleaned['author_role']);
        self::assertNull($cleaned['author_zone']);
        self::assertNull($cleaned['created_at']);
        self::assertNull($cleaned['updated_at']);
    }

    public function testCleanNullsImageFields(): void
    {
        $cleaned = $this->cleaner->cleanActionData($this->rawAction());

        self::assertNull($cleaned['image']);
        self::assertNull($cleaned['image_url']);
    }
}
