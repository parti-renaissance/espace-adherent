<?php

declare(strict_types=1);

namespace Tests\App\Normalizer;

use App\Entity\Adherent;
use App\Entity\Poll\Choice;
use App\Entity\Poll\Poll;
use App\Entity\Poll\PollResultDisplayModeEnum;
use App\Entity\Poll\Vote;
use App\Normalizer\PollNormalizer;
use App\Repository\Poll\VoteRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PollNormalizerTest extends TestCase
{
    public function testVoterGetsPercentagesButCountHiddenBelowThreshold(): void
    {
        $poll = $this->finishedPoll(participantCountThreshold: 5);
        $this->addVotes($poll, 2);

        $normalized = $this->normalizeAsVoter($poll, ['participant_count' => 2]);

        self::assertArrayHasKey('result', $normalized);
        self::assertSame(100.0, $normalized['result']['choices'][0]['percentage']);
        self::assertArrayNotHasKey('count', $normalized['result']['choices'][0]);
        self::assertArrayNotHasKey('total', $normalized['result']);
        self::assertArrayNotHasKey('participant_count', $normalized);
    }

    public function testVoterGetsCountWhenThresholdExceeded(): void
    {
        $poll = $this->finishedPoll(participantCountThreshold: 1);
        $this->addVotes($poll, 2);

        $normalized = $this->normalizeAsVoter($poll, ['participant_count' => 2]);

        self::assertSame(100.0, $normalized['result']['choices'][0]['percentage']);
        self::assertSame(2, $normalized['result']['choices'][0]['count']);
        self::assertSame(2, $normalized['result']['total']);
        self::assertSame(2, $normalized['participant_count']);
    }

    public function testDisconnectedUserGetsNoPercentages(): void
    {
        $poll = $this->finishedPoll(participantCountThreshold: 0);
        $this->addVotes($poll, 2);

        $normalized = $this->normalizeAsDisconnected($poll, ['participant_count' => 2]);

        self::assertArrayNotHasKey('result', $normalized);
        self::assertFalse($normalized['has_voted']);
        self::assertSame(2, $normalized['participant_count']);
    }

    private function normalizeAsVoter(Poll $poll, array $base): array
    {
        $vote = $this->createStub(Vote::class);
        $vote->method('getChoice')->willReturn($poll->getChoices()->first());
        $vote->method('getCreatedAt')->willReturn(new \DateTimeImmutable('2026-06-30 12:00:00'));

        return $this->normalize($poll, $base, $this->createStub(Adherent::class), $vote);
    }

    private function normalizeAsDisconnected(Poll $poll, array $base): array
    {
        return $this->normalize($poll, $base, null, null);
    }

    private function normalize(Poll $poll, array $base, ?Adherent $user, ?Vote $vote): array
    {
        $decorated = $this->createStub(NormalizerInterface::class);
        $decorated->method('normalize')->willReturn($base);

        $voteRepository = $this->createStub(VoteRepository::class);
        $voteRepository->method('findOneBy')->willReturn($vote);
        $voteRepository->method('findLatestVotersWithImage')->willReturn([]);

        $security = $this->createStub(Security::class);
        $security->method('getUser')->willReturn($user);

        $normalizer = new PollNormalizer(
            $this->createStub(UrlGeneratorInterface::class),
            $voteRepository,
            $security,
        );
        $normalizer->setNormalizer($decorated);

        return $normalizer->normalize($poll);
    }

    private function finishedPoll(int $participantCountThreshold): Poll
    {
        $poll = new Poll(
            question: 'Plutôt thé ou café ?',
            finishAt: new \DateTimeImmutable('-1 hour'),
            published: true,
            startAt: new \DateTimeImmutable('-2 hours'),
            resultDisplayEndAt: new \DateTimeImmutable('+1 hour'),
            participantCountThreshold: $participantCountThreshold,
            resultDisplayMode: PollResultDisplayModeEnum::AFTER_POLL,
        );

        $poll->addChoice(new Choice('Thé'));

        return $poll;
    }

    private function addVotes(Poll $poll, int $count): void
    {
        $choice = $poll->getChoices()->first();

        for ($i = 0; $i < $count; ++$i) {
            $choice->addVote(new Vote($poll, $choice, new Adherent()));
        }
    }
}
