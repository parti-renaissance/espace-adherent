<?php

namespace App\Command;

use App\Adherent\DeclaredMandateHistoryNotifier;
use App\Entity\Geo\Zone;
use App\Repository\AdherentRepository;
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
        private readonly AdherentRepository $adherentRepository,
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
        $notNotifiedHistories = $this->declaredMandateHistoryRepository->findNotNotified();

        if (!$notNotifiedHistories) {
            $this->io->text('No new declared mandate history.');

            return self::SUCCESS;
        }

        $this->notifyAdministrators($notNotifiedHistories);
        $this->notifyElectedRepresentativeManagers($notNotifiedHistories);
        $this->markHistoriesAsNotified($notNotifiedHistories);

        $this->io->success('Notifications sent!');

        return self::SUCCESS;
    }

    private function notifyAdministrators(array $histories): void
    {
        $administrators = $this->administratorRepository->findWithRole(self::ADMIN_ROLE_TO_NOTIFY);

        if (!$administrators) {
            $this->io->text('No administrator to notify.');

            return;
        }

        $this->io->text(sprintf(
            'Will notify %d administrators about %d new declared mandate historie(s)',
            \count($administrators),
            \count($histories)
        ));
        $this->io->progressStart(\count($administrators));

        foreach ($administrators as $administrator) {
            $this->declaredMandateHistoryNotifier->notifyAdministrator($administrator, $histories);

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
    }

    private function notifyElectedRepresentativeManagers(array $histories): void
    {
        $historiesByDepartment = [];
        foreach ($histories as $history) {
            $departments = $history->getAdherent()->getZonesOfType(Zone::DEPARTMENT, true);
            $department = !empty($departments) ? reset($departments) : null;

            if (!$department) {
                continue;
            }

            $historiesByDepartment[$department->getCode()][] = $history;
        }

        $electedRepresentativeManagers = $this->adherentRepository->findElectedRepresentativeManagersForDepartmentCodes(array_keys($historiesByDepartment));

        $notifiedPads = [];
        foreach ($electedRepresentativeManagers as $electedRepresentativeManager) {
            $departmentCode = $electedRepresentativeManager['department_code'];
            $padEmail = $electedRepresentativeManager['pad_email'];

            if (!\array_key_exists($departmentCode, $notifiedPads) || !\in_array($padEmail, $notifiedPads[$departmentCode])) {
                $this->io->text(sprintf(
                    'Will notify PAD of department %s about %d new declared mandate historie(s)',
                    $departmentCode,
                    \count($historiesByDepartment[$departmentCode])
                ));

                $this->declaredMandateHistoryNotifier->notifyAdherent($padEmail, $historiesByDepartment[$departmentCode]);

                $notifiedPads[$departmentCode][] = $padEmail;
            }

            if ($memberEmail = $electedRepresentativeManager['member_email']) {
                $this->io->text(sprintf(
                    'Will notify PAD delegated member of department %s about %d new declared mandate historie(s)',
                    $departmentCode,
                    \count($historiesByDepartment[$departmentCode])
                ));

                $this->declaredMandateHistoryNotifier->notifyAdherent($memberEmail, $historiesByDepartment[$departmentCode]);
            }
        }
    }

    private function markHistoriesAsNotified(array $histories): void
    {
        $this->io->text(sprintf('Will mark %d new declared mandate histories as notified', \count($histories)));
        $this->io->progressStart(\count($histories));

        foreach ($histories as $declaredMandateHistory) {
            $declaredMandateHistory->setNotified();

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
