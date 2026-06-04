<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\Indexer;

use App\JeMengage\Timeline\Indexer\UserProfile;
use PHPUnit\Framework\TestCase;

final class UserProfileTest extends TestCase
{
    public function testJsonSerializeCastsUserIdToString(): void
    {
        $body = new UserProfile(123456, [], [], [], [], [], [], 0, [])->jsonSerialize();

        // The ranker validates user_id as a JSON string; an integer body yields a 400. The id stays a
        // native int on the property/constructor — the cast happens only at the serialization boundary.
        self::assertIsString($body['user_id']);
        self::assertSame('123456', $body['user_id']);
    }
}
