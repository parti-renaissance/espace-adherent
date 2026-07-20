<?php

declare(strict_types=1);

namespace Tests\App\Command;

use App\Command\DispatchPollNotificationsCommand;
use App\Entity\Poll\Poll;
use App\JeMengage\Push\Command\PollNotificationCommand;
use App\Poll\PollReminderTypeEnum;
use App\Repository\Poll\PollRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

final class DispatchPollNotificationsCommandTest extends TestCase
{
    public function testDispatchesLaunchWhenVoteJustOpened(): void
    {
        $now = $this->parisTime('2026-07-14 10:00:00');
        $poll = $this->createPoll($now, '-1 hour', '+3 days');

        $dispatched = $this->runCommand($poll, $now);

        self::assertCount(1, $dispatched);
        self::assertSame(PollReminderTypeEnum::LAUNCH, $dispatched[0]->type);
        self::assertTrue($poll->hasReminderBeenSent(PollReminderTypeEnum::LAUNCH));
    }

    public function testDispatchesReminderJ1EightHoursBeforeClosing(): void
    {
        $now = $this->parisTime('2026-07-14 10:00:00');
        $poll = $this->createPoll($now, '-5 days', '+2 hours');
        $poll->markReminderSent(PollReminderTypeEnum::LAUNCH);

        $dispatched = $this->runCommand($poll, $now);

        self::assertCount(1, $dispatched);
        self::assertSame(PollReminderTypeEnum::REMINDER_H8, $dispatched[0]->type);
    }

    public function testDoesNotDispatchReminderH8MoreThanEightHoursBeforeClosing(): void
    {
        $now = $this->parisTime('2026-07-14 10:00:00');
        $poll = $this->createPoll($now, '-1 hour', '+12 hours');
        $poll->markReminderSent(PollReminderTypeEnum::LAUNCH);

        self::assertCount(0, $this->runCommand($poll, $now));
    }

    public function testDispatchesClosingH1WithinLastHour(): void
    {
        $now = $this->parisTime('2026-07-14 10:00:00');
        $poll = $this->createPoll($now, '-5 days', '+30 minutes');
        $poll->markReminderSent(PollReminderTypeEnum::LAUNCH);
        $poll->markReminderSent(PollReminderTypeEnum::REMINDER_H8);

        $dispatched = $this->runCommand($poll, $now);

        self::assertCount(1, $dispatched);
        self::assertSame(PollReminderTypeEnum::CLOSING_H1, $dispatched[0]->type);
    }

    public function testDoesNotRedispatchAlreadySentReminders(): void
    {
        $now = $this->parisTime('2026-07-14 10:00:00');
        $poll = $this->createPoll($now, '-5 days', '+30 minutes');
        $poll->markReminderSent(PollReminderTypeEnum::LAUNCH);
        $poll->markReminderSent(PollReminderTypeEnum::REMINDER_H8);
        $poll->markReminderSent(PollReminderTypeEnum::CLOSING_H1);

        self::assertCount(0, $this->runCommand($poll, $now));
    }

    private function parisTime(string $time): \DateTimeImmutable
    {
        return new \DateTimeImmutable($time, new \DateTimeZone('Europe/Paris'));
    }

    private function createPoll(\DateTimeImmutable $now, string $startAt, string $finishAt): Poll
    {
        return new Poll(
            Uuid::v4(),
            'Plutôt thé ou café ?',
            $now->modify($finishAt),
            true,
            $now->modify($startAt),
        );
    }

    /**
     * @return PollNotificationCommand[]
     */
    private function runCommand(Poll $poll, \DateTimeImmutable $now): array
    {
        $bus = new class implements MessageBusInterface {
            public array $dispatched = [];

            public function dispatch(object $message, array $stamps = []): Envelope
            {
                $this->dispatched[] = $message;

                return new Envelope($message);
            }
        };

        $repository = $this->createStub(PollRepository::class);
        $repository->method('findPollsPendingNotification')->willReturn([$poll]);

        $command = new DispatchPollNotificationsCommand(
            $bus,
            $repository,
            $this->createStub(EntityManagerInterface::class),
            new MockClock($now),
        );

        new CommandTester($command)->execute([]);

        return $bus->dispatched;
    }
}
