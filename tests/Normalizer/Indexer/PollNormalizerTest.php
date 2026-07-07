<?php

declare(strict_types=1);

namespace Tests\App\Normalizer\Indexer;

use App\Entity\Poll\Poll;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use App\Normalizer\Indexer\PollNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

final class PollNormalizerTest extends TestCase
{
    public function testPollIsNormalizedAsNationalTimelineFeed(): void
    {
        $startAt = new \DateTimeImmutable('2026-07-10 14:00:00');
        $finishAt = new \DateTimeImmutable('2026-07-12 18:00:00');

        $poll = new Poll(
            Uuid::fromString('8adca369-938c-450b-92e9-9c2b1f206fa3'),
            'Plutôt thé ou café ?',
            $finishAt,
            true,
            $startAt,
            description: 'Un choix difficile.',
        );

        $normalized = new PollNormalizer($this->createStub(UrlGeneratorInterface::class))->normalize($poll);

        self::assertSame(TimelineFeedTypeEnum::POLL, $normalized['type']);
        self::assertSame('8adca369-938c-450b-92e9-9c2b1f206fa3', $normalized['identifier']);
        self::assertSame('Plutôt thé ou café ?', $normalized['title']);
        self::assertSame('Un choix difficile.', $normalized['description']);
        self::assertSame($startAt->format('c'), $normalized['date']);
        self::assertSame($startAt->format('c'), $normalized['begin_at']);
        self::assertSame($finishAt->format('c'), $normalized['finish_at']);
        self::assertTrue($normalized['is_national']);
        self::assertSame('Je participe', $normalized['cta_label']);
        self::assertNull($normalized['author']['uuid']);
        self::assertNull($normalized['zone_codes']);
    }

    public function testSupportsOnlyPollOnSearchableFormat(): void
    {
        $normalizer = new PollNormalizer($this->createStub(UrlGeneratorInterface::class));

        self::assertTrue($normalizer->supportsNormalization(new Poll(), 'searchableArray'));
        self::assertFalse($normalizer->supportsNormalization(new Poll(), 'json'));
        self::assertFalse($normalizer->supportsNormalization(new \stdClass(), 'searchableArray'));
    }
}
