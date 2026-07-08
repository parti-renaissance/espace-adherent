<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\FeedProcessor;

use App\Entity\Adherent;
use App\Entity\Poll\Vote;
use App\JeMengage\Timeline\FeedProcessor\PollProcessor;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use App\Repository\Poll\VoteRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class PollProcessorTest extends TestCase
{
    private const string POLL_UUID = '8adca369-938c-450b-92e9-9c2b1f206fa3';
    private const string QUESTION = 'Plutôt thé ou café ?';

    public function testSupportsOnlyPollItems(): void
    {
        $processor = new PollProcessor($this->createStub(VoteRepository::class));
        $user = $this->createStub(Adherent::class);

        self::assertTrue($processor->supports(['type' => TimelineFeedTypeEnum::POLL], $user));
        self::assertFalse($processor->supports(['type' => TimelineFeedTypeEnum::NEWS], $user));
        self::assertFalse($processor->supports([], $user));
    }

    public function testProcessSurfacesVoteDateAndVotedPollBlock(): void
    {
        $votedAt = new \DateTimeImmutable('2026-07-08 10:12:00');
        $user = $this->createStub(Adherent::class);

        $vote = $this->createStub(Vote::class);
        $vote->method('getCreatedAt')->willReturn($votedAt);

        $voteRepository = $this->createMock(VoteRepository::class);
        $voteRepository
            ->expects($this->once())
            ->method('findAdherentVote')
            ->with(Uuid::fromString(self::POLL_UUID), $user)
            ->willReturn($vote)
        ;

        $item = new PollProcessor($voteRepository)->process(
            ['type' => TimelineFeedTypeEnum::POLL, 'objectID' => self::POLL_UUID, 'title' => self::QUESTION],
            $user,
        );

        self::assertSame($votedAt, $item['user_registered_at']);
        self::assertSame(['question' => self::QUESTION, 'has_voted' => true], $item['poll']);
    }

    public function testProcessMarksNotVotedWhenUserHasNoVote(): void
    {
        $voteRepository = $this->createStub(VoteRepository::class);
        $voteRepository->method('findAdherentVote')->willReturn(null);

        $item = new PollProcessor($voteRepository)->process(
            ['type' => TimelineFeedTypeEnum::POLL, 'objectID' => self::POLL_UUID, 'title' => self::QUESTION],
            $this->createStub(Adherent::class),
        );

        self::assertNull($item['user_registered_at']);
        self::assertSame(['question' => self::QUESTION, 'has_voted' => false], $item['poll']);
    }
}
