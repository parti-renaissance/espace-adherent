<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\FeedProcessor;

use App\Api\Serializer\PrivatePublicContextBuilder;
use App\Entity\Adherent;
use App\Entity\Poll\Poll;
use App\Entity\Poll\Vote;
use App\JeMengage\Timeline\FeedProcessor\PollProcessor;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use App\Repository\Poll\PollRepository;
use App\Repository\Poll\VoteRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Uid\Uuid;

final class PollProcessorTest extends TestCase
{
    private const string POLL_UUID = '8adca369-938c-450b-92e9-9c2b1f206fa3';

    public function testSupportsOnlyPollItems(): void
    {
        $processor = new PollProcessor(
            $this->createStub(PollRepository::class),
            $this->createStub(VoteRepository::class),
            $this->createStub(NormalizerInterface::class),
        );
        $user = $this->createStub(Adherent::class);

        self::assertTrue($processor->supports(['type' => TimelineFeedTypeEnum::POLL], $user));
        self::assertFalse($processor->supports(['type' => TimelineFeedTypeEnum::NEWS], $user));
        self::assertFalse($processor->supports([], $user));
    }

    public function testProcessSurfacesVoteDateAndNormalizedPoll(): void
    {
        $votedAt = new \DateTimeImmutable('2026-07-08 10:12:00');
        $user = $this->createStub(Adherent::class);
        $poll = $this->createStub(Poll::class);
        $normalizedPoll = ['question' => 'Plutôt thé ou café ?', 'has_voted' => true, 'voted_at' => '2026-07-08T10:12:00+02:00'];

        $vote = $this->createStub(Vote::class);
        $vote->method('getCreatedAt')->willReturn($votedAt);

        $pollRepository = $this->createMock(PollRepository::class);
        $pollRepository
            ->expects($this->once())
            ->method('findOneByUuid')
            ->with(self::POLL_UUID)
            ->willReturn($poll)
        ;

        $voteRepository = $this->createMock(VoteRepository::class);
        $voteRepository
            ->expects($this->once())
            ->method('findAdherentVote')
            ->with(Uuid::fromString(self::POLL_UUID), $user)
            ->willReturn($vote)
        ;

        $normalizer = $this->createMock(NormalizerInterface::class);
        $normalizer
            ->expects($this->once())
            ->method('normalize')
            ->with($poll, null, [
                'groups' => ['poll_read'],
                PrivatePublicContextBuilder::CONTEXT_KEY => PrivatePublicContextBuilder::CONTEXT_PUBLIC_CONNECTED_USER,
            ])
            ->willReturn($normalizedPoll)
        ;

        $item = new PollProcessor($pollRepository, $voteRepository, $normalizer)->process(
            ['type' => TimelineFeedTypeEnum::POLL, 'objectID' => self::POLL_UUID],
            $user,
        );

        self::assertSame($votedAt, $item['user_registered_at']);
        self::assertSame($normalizedPoll, $item['poll']);
    }

    public function testProcessReturnsItemUnchangedWhenPollNotFound(): void
    {
        $pollRepository = $this->createStub(PollRepository::class);
        $pollRepository->method('findOneByUuid')->willReturn(null);

        $item = new PollProcessor(
            $pollRepository,
            $this->createStub(VoteRepository::class),
            $this->createStub(NormalizerInterface::class),
        )->process(
            ['type' => TimelineFeedTypeEnum::POLL, 'objectID' => self::POLL_UUID],
            $this->createStub(Adherent::class),
        );

        self::assertArrayNotHasKey('poll', $item);
        self::assertArrayNotHasKey('user_registered_at', $item);
    }
}
