<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\FeedProcessor;

use App\Api\Serializer\PrivatePublicContextBuilder;
use App\Entity\Adherent;
use App\Entity\Poll\Poll;
use App\JeMengage\Timeline\FeedProcessor\PollProcessor;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use App\Repository\Poll\PollRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PollProcessorTest extends TestCase
{
    public function testSupportsOnlyPollItems(): void
    {
        $processor = new PollProcessor($this->createStub(PollRepository::class), $this->createStub(NormalizerInterface::class));
        $user = $this->createStub(Adherent::class);

        self::assertTrue($processor->supports(['type' => TimelineFeedTypeEnum::POLL], $user));
        self::assertFalse($processor->supports(['type' => TimelineFeedTypeEnum::NEWS], $user));
        self::assertFalse($processor->supports([], $user));
    }

    public function testProcessAttachesLivePollPayload(): void
    {
        $poll = $this->poll(new \DateTimeImmutable('-1 hour'), new \DateTimeImmutable('+1 day'));

        $pollRepository = $this->createMock(PollRepository::class);
        $pollRepository
            ->expects($this->once())
            ->method('findOneByUuid')
            ->with('8adca369-938c-450b-92e9-9c2b1f206fa3')
            ->willReturn($poll)
        ;

        $normalizer = $this->createMock(NormalizerInterface::class);
        $normalizer
            ->expects($this->once())
            ->method('normalize')
            ->with($poll, 'json', [
                'groups' => ['poll_read'],
                PrivatePublicContextBuilder::CONTEXT_KEY => PrivatePublicContextBuilder::CONTEXT_PUBLIC_CONNECTED_USER,
            ])
            ->willReturn(['question' => 'Plutôt thé ou café ?', 'has_voted' => false])
        ;

        $item = new PollProcessor($pollRepository, $normalizer)->process(
            ['type' => TimelineFeedTypeEnum::POLL, 'identifier' => '8adca369-938c-450b-92e9-9c2b1f206fa3'],
            $this->createStub(Adherent::class),
        );

        self::assertSame(['question' => 'Plutôt thé ou café ?', 'has_voted' => false], $item['poll']);
    }

    public function testProcessLeavesItemUntouchedWhenIdentifierIsInvalid(): void
    {
        $pollRepository = $this->createMock(PollRepository::class);
        $pollRepository->expects($this->never())->method('findOneByUuid');

        $normalizer = $this->createMock(NormalizerInterface::class);
        $normalizer->expects($this->never())->method('normalize');

        $item = ['type' => TimelineFeedTypeEnum::POLL, 'identifier' => 'not-a-uuid'];

        self::assertSame($item, new PollProcessor($pollRepository, $normalizer)->process($item, $this->createStub(Adherent::class)));
    }

    public function testProcessLeavesItemUntouchedWhenPollIsNotFound(): void
    {
        $pollRepository = $this->createStub(PollRepository::class);
        $pollRepository->method('findOneByUuid')->willReturn(null);

        $normalizer = $this->createMock(NormalizerInterface::class);
        $normalizer->expects($this->never())->method('normalize');

        $item = ['type' => TimelineFeedTypeEnum::POLL, 'identifier' => '8adca369-938c-450b-92e9-9c2b1f206fa3'];

        self::assertSame($item, new PollProcessor($pollRepository, $normalizer)->process($item, $this->createStub(Adherent::class)));
    }

    private function poll(\DateTimeImmutable $startAt, \DateTimeImmutable $finishAt): Poll
    {
        return new Poll(null, 'Plutôt thé ou café ?', $finishAt, true, $startAt);
    }
}
