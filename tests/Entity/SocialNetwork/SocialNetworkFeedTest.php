<?php

declare(strict_types=1);

namespace Tests\App\Entity\SocialNetwork;

use App\Entity\SocialNetwork\SocialNetworkFeed;
use PHPUnit\Framework\TestCase;

final class SocialNetworkFeedTest extends TestCase
{
    public function testIsIndexableReflectsPublished(): void
    {
        $feed = new SocialNetworkFeed();

        self::assertFalse($feed->isIndexable());

        $feed->published = true;

        self::assertTrue($feed->isIndexable());
    }
}
