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
    description: 'Dispatch pronostic push notifications (creation, J-1, H-1, results) for the displayed pronostic.',
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
        $oneDayBefore = \DateTimeImmutable::createFromInterface($pronostic->matchAt)->modify('-1 day');
        $oneHourBefore = \DateTimeImmutable::createFromInterface($pronostic->matchAt)->modify('-1 hour');

        if ($pronostic->beginAt < $oneDayBefore && $now >= $pronostic->beginAt && !$pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::CREATION)) {
            $this->dispatch($pronostic, PronosticReminderTypeEnum::CREATION);
            $this->io->success('Push de création programmé.');
        }

        if ($now >= $pronostic->beginAt && $now < $pronostic->matchAt) {
            if ($now >= $oneDayBefore && !$pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::J_MINUS_1)) {
                $this->dispatch($pronostic, PronosticReminderTypeEnum::J_MINUS_1);
                $this->io->success('Push J-1 programmé.');
            }

            if ($now >= $oneHourBefore && !$pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::H_MINUS_1)) {
                $this->dispatch($pronostic, PronosticReminderTypeEnum::H_MINUS_1);
                $this->io->success('Push H-1 programmé.');
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
