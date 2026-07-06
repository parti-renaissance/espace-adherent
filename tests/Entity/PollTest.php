<?php

declare(strict_types=1);

namespace Tests\App\Entity;

use App\Entity\Adherent;
use App\Entity\Poll\Choice;
use App\Entity\Poll\Poll;
use App\Entity\Poll\PollResultDisplayModeEnum;
use App\Entity\Poll\Vote;
use PHPUnit\Framework\TestCase;

class PollTest extends TestCase
{
    public function testResultIsDisplayedToVoterWhilePollIsVisibleInAfterVoteMode(): void
    {
        $poll = $this->createPoll(PollResultDisplayModeEnum::AFTER_VOTE);
        $this->addVotes($poll, 1);

        self::assertTrue($poll->canDisplayResult(new \DateTimeImmutable('2026-06-30 12:00:00'), true));
        self::assertTrue($poll->canDisplayResult(new \DateTimeImmutable('2026-06-30 14:00:00'), true));
        self::assertFalse($poll->canDisplayResult(new \DateTimeImmutable('2026-06-30 16:00:00'), true));
    }

    public function testResultIsHiddenDuringVotePeriodWhenUserHasNotVotedInAfterVoteMode(): void
    {
        $poll = $this->createPoll(PollResultDisplayModeEnum::AFTER_VOTE);
        $this->addVotes($poll, 1);

        self::assertFalse($poll->canDisplayResult(new \DateTimeImmutable('2026-06-30 12:00:00')));
        self::assertTrue($poll->canDisplayResult(new \DateTimeImmutable('2026-06-30 14:00:00')));
    }

    public function testResultCanBeDisplayedAfterPollEnd(): void
    {
        $poll = $this->createPoll(PollResultDisplayModeEnum::AFTER_POLL);
        $this->addVotes($poll, 1);

        self::assertFalse($poll->canDisplayResult(new \DateTimeImmutable('2026-06-30 12:00:00'), true));
        self::assertTrue($poll->canDisplayResult(new \DateTimeImmutable('2026-06-30 14:00:00'), true));
        self::assertFalse($poll->canDisplayResult(new \DateTimeImmutable('2026-06-30 16:00:00'), true));
    }

    public function testResultCanNeverBeDisplayed(): void
    {
        $poll = $this->createPoll(PollResultDisplayModeEnum::NEVER);
        $this->addVotes($poll, 1);

        self::assertFalse($poll->canDisplayResult(new \DateTimeImmutable('2026-06-30 12:00:00'), true));
        self::assertFalse($poll->canDisplayResult(new \DateTimeImmutable('2026-06-30 14:00:00'), true));
    }

    public function testParticipantThresholdPreventsResultDisplay(): void
    {
        $poll = $this->createPoll(PollResultDisplayModeEnum::AFTER_VOTE, participantCountThreshold: 2);
        $this->addVotes($poll, 1);

        self::assertFalse($poll->canDisplayResult(new \DateTimeImmutable('2026-06-30 12:00:00'), true));

        $this->addVotes($poll, 1);

        self::assertTrue($poll->canDisplayResult(new \DateTimeImmutable('2026-06-30 12:00:00'), true));
    }

    public function testUnpublishedPollNeverDisplaysResult(): void
    {
        $poll = $this->createPoll(PollResultDisplayModeEnum::AFTER_VOTE, published: false);
        $this->addVotes($poll, 1);

        self::assertFalse($poll->canDisplayResult(new \DateTimeImmutable('2026-06-30 12:00:00'), true));
        self::assertFalse($poll->isVotePeriodActive(new \DateTimeImmutable('2026-06-30 12:00:00')));
    }

    private function createPoll(
        PollResultDisplayModeEnum $resultDisplayMode,
        int $participantCountThreshold = 0,
        bool $published = true,
    ): Poll {
        $poll = new Poll(
            question: 'Question ?',
            finishAt: new \DateTimeImmutable('2026-06-30 13:00:00'),
            published: $published,
            startAt: new \DateTimeImmutable('2026-06-30 11:00:00'),
            resultDisplayEndAt: new \DateTimeImmutable('2026-06-30 15:00:00'),
            participantCountThreshold: $participantCountThreshold,
            resultDisplayMode: $resultDisplayMode
        );

        $poll->addChoice(new Choice('Oui'));

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
