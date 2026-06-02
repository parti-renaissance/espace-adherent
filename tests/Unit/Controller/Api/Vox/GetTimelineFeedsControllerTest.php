<?php

declare(strict_types=1);

namespace Tests\App\Unit\Controller\Api\Vox;

use App\Controller\Api\Vox\GetTimelineFeedsController;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use PHPUnit\Framework\TestCase;

final class GetTimelineFeedsControllerTest extends TestCase
{
    public function testTimelineFeedTypesExposeSocialNetworkPosts(): void
    {
        // The tag filter list is private; reflection asserts the controller advertises the type to
        // Algolia. The Behat fixture SearchService ignores tagFilters, so it cannot cover this.
        $types = new \ReflectionClass(GetTimelineFeedsController::class)->getConstant('TIMELINE_FEED_TYPES');

        self::assertIsArray($types);
        self::assertContains(TimelineFeedTypeEnum::SOCIAL_NETWORK_POST, $types);
        // Guard against accidentally dropping the existing types when editing the list.
        self::assertContains(TimelineFeedTypeEnum::NEWS, $types);
        self::assertContains(TimelineFeedTypeEnum::PUBLICATION, $types);
    }
}
