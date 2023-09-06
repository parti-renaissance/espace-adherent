<?php

namespace App\Command;

use App\Adherent\DeclaredMandateHistoryNotifier;
use App\Repository\AdministratorRepository;
use App\Repository\Reporting\DeclaredMandateHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:declared-mandates:notify-changes',
    description: 'This command send notifications about changes in adherent declared mandates',
)]
class SendDeclaredMandateChangeNotificationCommand extends Command
{
    private const ADMIN_ROLE_TO_NOTIFY = 'ROLE_ADMIN_ELUS_NOTIFICATION';

    private ?SymfonyStyle $io = null;

    public function __construct(
        private readonly DeclaredMandateHistoryRepository $declaredMandateHistoryRepository,
        private readonly AdministratorRepository $administratorRepository,
        private readonly DeclaredMandateHistoryNotifier $declaredMandateHistoryNotifier,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $administrators = $this->administratorRepository->findWithRole(self::ADMIN_ROLE_TO_NOTIFY);

        if (!$administrators) {
            $this->io->text('No administrator to notify.');

            return self::SUCCESS;
        }

        $notNotifiedHistories = $this->declaredMandateHistoryRepository->findNotNotified();

        if (!$notNotifiedHistories) {
            $this->io->text('No new declared mandate history.');

            return self::SUCCESS;
        }

        $this->io->text(sprintf(
            'Will notify %d administrators about %d new declared mandate histories',
            \count($administrators),
            \count($notNotifiedHistories)
        ));
        $this->io->progressStart(\count($administrators));

        foreach ($administrators as $administrator) {
            $this->declaredMandateHistoryNotifier->notifyAdministrator($administrator, $notNotifiedHistories);

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->text(sprintf('Will mark %d new declared mandate histories as notified', \count($notNotifiedHistories)));
        $this->io->progressStart(\count($notNotifiedHistories));

        foreach ($notNotifiedHistories as $declaredMandateHistory) {
            $declaredMandateHistory->setNotified();

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->io->success('Notifications sent!');

        return self::SUCCESS;
    }
}
