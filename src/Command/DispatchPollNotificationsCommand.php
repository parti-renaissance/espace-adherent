<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Poll\Poll;
use App\JeMengage\Push\Command\PollNotificationCommand;
use App\Poll\PollReminderTypeEnum;
use App\Repository\Poll\PollRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:poll:dispatch-notifications',
    description: 'Dispatch poll push notifications (launch, H-8 reminder, H-1 reminder) for published polls.',
)]
class DispatchPollNotificationsCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly PollRepository $pollRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ClockInterface $clock,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = $this->clock->now();

        foreach ($this->pollRepository->findPollsPendingNotification() as $poll) {
            $startAt = $poll->getStartAt();
            $finishAt = $poll->getFinishAt();

            if (null === $startAt || null === $finishAt) {
                continue;
            }

            $this->dispatchLaunch($poll, $startAt, $finishAt, $now);
            $this->dispatchReminderH8($poll, $finishAt, $now);
            $this->dispatchClosingH1($poll, $finishAt, $now);
        }

        return self::SUCCESS;
    }

    private function dispatchLaunch(Poll $poll, \DateTimeImmutable $startAt, \DateTimeImmutable $finishAt, \DateTimeImmutable $now): void
    {
        if ($poll->hasReminderBeenSent(PollReminderTypeEnum::LAUNCH)) {
            return;
        }

        if ($now >= $startAt && $now < $finishAt) {
            $this->dispatch($poll, PollReminderTypeEnum::LAUNCH);
        }
    }

    private function dispatchReminderH8(Poll $poll, \DateTimeImmutable $finishAt, \DateTimeImmutable $now): void
    {
        if ($poll->hasReminderBeenSent(PollReminderTypeEnum::REMINDER_H8)) {
            return;
        }

        $startAt = $poll->getStartAt();
        $reminderAt = $finishAt->modify('-8 hours');

        if (null !== $startAt && $startAt <= $reminderAt && $now >= $reminderAt && $now < $finishAt) {
            $this->dispatch($poll, PollReminderTypeEnum::REMINDER_H8);
        }
    }

    private function dispatchClosingH1(Poll $poll, \DateTimeImmutable $finishAt, \DateTimeImmutable $now): void
    {
        if ($poll->hasReminderBeenSent(PollReminderTypeEnum::CLOSING_H1)) {
            return;
        }

        $startAt = $poll->getStartAt();
        $closingAt = $finishAt->modify('-1 hour');

        if (null !== $startAt && $startAt <= $closingAt && $now >= $closingAt && $now < $finishAt) {
            $this->dispatch($poll, PollReminderTypeEnum::CLOSING_H1);
        }
    }

    private function dispatch(Poll $poll, PollReminderTypeEnum $type): void
    {
        $this->bus->dispatch(new PollNotificationCommand($poll->getUuid(), $type));
        $poll->markReminderSent($type);
        $this->entityManager->flush();
    }
}
