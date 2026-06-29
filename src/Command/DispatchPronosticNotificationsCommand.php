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
            $this->io->note('Aucun pronostic affiché.');

            return self::SUCCESS;
        }

        $now = new \DateTimeImmutable();
        $alertExpiresAt = \DateTimeImmutable::createFromInterface($pronostic->matchAt)->modify('+24 hours');

        if ($now >= $alertExpiresAt) {
            $pronostic->displayed = false;
            $this->entityManager->flush();
            $this->io->success('Pronostic masqué : le match est terminé depuis plus de 24 heures.');

            return self::SUCCESS;
        }

        $oneDayBefore = \DateTimeImmutable::createFromInterface($pronostic->matchAt)->modify('-1 day');
        $oneHourBefore = \DateTimeImmutable::createFromInterface($pronostic->matchAt)->modify('-1 hour');
        $fiveMinutesBefore = \DateTimeImmutable::createFromInterface($pronostic->matchAt)->modify('-5 minutes');

        if ($pronostic->beginAt < $oneDayBefore && $now >= $pronostic->beginAt && !$pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::CREATION)) {
            $jMinus1Skipped = $now >= $oneDayBefore;
            if ($jMinus1Skipped) {
                $pronostic->markReminderSent(PronosticReminderTypeEnum::J_MINUS_1);
            }

            $this->dispatch($pronostic, PronosticReminderTypeEnum::CREATION);
            $this->io->success('Push de création programmé.');

            if ($jMinus1Skipped) {
                $this->io->note('Push J-1 ignoré : seuil J-1 déjà dépassé.');
            }
        }

        if ($now >= $pronostic->beginAt && $now < $pronostic->matchAt) {
            if ($pronostic->beginAt <= $oneDayBefore && $now >= $oneDayBefore && !$pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::J_MINUS_1)) {
                $this->dispatch($pronostic, PronosticReminderTypeEnum::J_MINUS_1);
                $this->io->success('Push J-1 programmé.');
            }

            if ($now >= $oneHourBefore && !$pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::H_MINUS_1)) {
                $this->dispatch($pronostic, PronosticReminderTypeEnum::H_MINUS_1);
                $this->io->success('Push H-1 programmé.');
            }

            if ($now >= $fiveMinutesBefore && !$pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::H_MINUS_5_MIN)) {
                $this->dispatch($pronostic, PronosticReminderTypeEnum::H_MINUS_5_MIN);
                $this->io->success('Push H-5min programmé.');
            }
        }

        if ($pronostic->isResultPublished() && !$pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::RESULTS)) {
            $this->dispatch($pronostic, PronosticReminderTypeEnum::RESULTS);
            $this->io->success('Push de résultats programmé.');
        }

        return self::SUCCESS;
    }

    private function dispatch(Pronostic $pronostic, PronosticReminderTypeEnum $type): void
    {
        $this->bus->dispatch(new PronosticNotificationCommand($pronostic->getUuid(), $type));
        $pronostic->markReminderSent($type);
        $this->entityManager->flush();
    }
}
