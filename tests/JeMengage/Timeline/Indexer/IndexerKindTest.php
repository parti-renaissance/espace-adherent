<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\Indexer;

use App\JeMengage\Timeline\Indexer\IndexerKind;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use PHPUnit\Framework\TestCase;

class IndexerKindTest extends TestCase
{
    public function testPushableTypesMapToTheirKind(): void
    {
        self::assertSame(IndexerKind::EVENT, IndexerKind::fromInternalType(TimelineFeedTypeEnum::EVENT));
        self::assertSame(IndexerKind::ACTION, IndexerKind::fromInternalType(TimelineFeedTypeEnum::ACTION));
        self::assertSame(IndexerKind::SOCIAL_POST, IndexerKind::fromInternalType(TimelineFeedTypeEnum::SOCIAL_NETWORK_POST));
        self::assertSame(IndexerKind::PUBLICATION, IndexerKind::fromInternalType(TimelineFeedTypeEnum::PUBLICATION));
        self::assertSame(IndexerKind::NOTIFICATION, IndexerKind::fromInternalType(TimelineFeedTypeEnum::NEWS));
    }

    public function testNonPushableTypesReturnNull(): void
    {
        $nonPushable = [
            TimelineFeedTypeEnum::TRANSACTIONAL_MESSAGE,
            TimelineFeedTypeEnum::RIPOSTE,
            TimelineFeedTypeEnum::SURVEY,
            TimelineFeedTypeEnum::PAP_CAMPAIGN,
            TimelineFeedTypeEnum::PHONING_CAMPAIGN,
            'unknown-type',
        ];

        foreach ($nonPushable as $type) {
            self::assertNull(IndexerKind::fromInternalType($type), $type);
        }
    }
}
