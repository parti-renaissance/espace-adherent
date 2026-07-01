<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Pronostic\Pronostic;
use App\JeMengage\Push\Command\PronosticNotificationCommand;
use App\Pronostic\PronosticReminderTypeEnum;
use App\Repository\Pronostic\PronosticRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:pronostic:dispatch-notifications',
    description: 'Dispatch pronostic push notifications (creation, J-1, H-1, H-5min, results) for the displayed pronostic.',
)]
class DispatchPronosticNotificationsCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly PronosticRepository $pronosticRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pronostic = $this->pronosticRepository->findDisplayed();
        if (!$pronostic) {
            return self::SUCCESS;
        }

        $now = new \DateTimeImmutable();

        $this->dispatchCreationReminder($pronostic, $now);
        $this->dispatchPreMatchReminders($pronostic, $now);
        $this->dispatchResultReminder($pronostic);

        return self::SUCCESS;
    }

    private function dispatchCreationReminder(Pronostic $pronostic, \DateTimeImmutable $now): void
    {
        $oneDayBefore = \DateTimeImmutable::createFromInterface($pronostic->matchAt)->modify('-1 day');

        if ($now < $pronostic->beginAt || $pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::CREATION)) {
            return;
        }

        $jMinus1Skipped = $now >= $oneDayBefore;
        if ($jMinus1Skipped) {
            $pronostic->markReminderSent(PronosticReminderTypeEnum::J_MINUS_1);
        }

        $this->dispatch($pronostic, PronosticReminderTypeEnum::CREATION);
    }

    private function dispatchPreMatchReminders(Pronostic $pronostic, \DateTimeImmutable $now): void
    {
        if ($now < $pronostic->beginAt || $now >= $pronostic->matchAt) {
            return;
        }

        $oneDayBefore = \DateTimeImmutable::createFromInterface($pronostic->matchAt)->modify('-1 day');
        $oneHourBefore = \DateTimeImmutable::createFromInterface($pronostic->matchAt)->modify('-1 hour');
        $fiveMinutesBefore = \DateTimeImmutable::createFromInterface($pronostic->matchAt)->modify('-5 minutes');

        if ($now >= $fiveMinutesBefore && !$pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::H_MINUS_5_MIN)) {
            $this->dispatch($pronostic, PronosticReminderTypeEnum::H_MINUS_5_MIN);

            return;
        }

        if ($now >= $oneHourBefore && !$pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::H_MINUS_1)) {
            $this->dispatch($pronostic, PronosticReminderTypeEnum::H_MINUS_1);

            return;
        }

        if ($pronostic->beginAt <= $oneDayBefore && $now >= $oneDayBefore && !$pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::J_MINUS_1)) {
            $this->dispatch($pronostic, PronosticReminderTypeEnum::J_MINUS_1);
        }
    }

    private function dispatchResultReminder(Pronostic $pronostic): void
    {
        if (!$pronostic->isResultPublished() || $pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::RESULTS)) {
            return;
        }

        $this->dispatch($pronostic, PronosticReminderTypeEnum::RESULTS);
    }

    private function dispatch(Pronostic $pronostic, PronosticReminderTypeEnum $type): void
    {
        $this->bus->dispatch(new PronosticNotificationCommand($pronostic->getUuid(), $type));
        $pronostic->markReminderSent($type);
        $this->entityManager->flush();
    }
}
