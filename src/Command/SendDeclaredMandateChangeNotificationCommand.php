<?php

declare(strict_types=1);

namespace App\Command;

use App\Adherent\DeclaredMandateHistoryNotifier;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\Reporting\DeclaredMandateHistory;
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
    private const ADMIN_ROLE_TO_NOTIFY = 'ROLE_ADMIN_TERRITOIRES_ELUS_NOTIFICATION';
    private const DEPARTMENT_CODE_EXCEPTIONS = [
        '64PB',
        '64B',
        '2A',
        '2B',
    ];

    private ?SymfonyStyle $io = null;

    public function __construct(
        private readonly DeclaredMandateHistoryRepository $declaredMandateHistoryRepository,
        private readonly AdministratorRepository $administratorRepository,
        private readonly AdherentRepository $adherentRepository,
        private readonly DeclaredMandateHistoryNotifier $declaredMandateHistoryNotifier,
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
        $notNotifiedHistories = $this->declaredMandateHistoryRepository->findToNotify();

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

        $this->io->text(\sprintf(
            'Will notify %d administrator(s) about %d new declared mandate historie(s)',
            \count($administrators),
            \count($histories)
        ));

        $this->declaredMandateHistoryNotifier->notifyAdministrators($administrators, $histories);
    }

    private function notifyElectedRepresentativeManagers(array $histories): void
    {
        $historiesByDepartment = [];
        foreach ($histories as $history) {
            $department = $this->getDepartment($history->getAdherent());

            if (!$department) {
                continue;
            }

            $historiesByDepartment[$department->getCode()][] = $history;
        }

        $electedRepresentativeManagers = $this->adherentRepository->findElectedRepresentativeManagersForDepartmentCodes(array_keys($historiesByDepartment));

        $recipients = [];
        foreach ($electedRepresentativeManagers as $electedRepresentativeManager) {
            $departmentCode = $electedRepresentativeManager['department_code'];
            $padEmail = $electedRepresentativeManager['pad_email'];
            $memberEmail = $electedRepresentativeManager['member_email'];

            if (!\array_key_exists($departmentCode, $recipients)) {
                $recipients[$departmentCode] = [$padEmail];
            }

            if ($memberEmail) {
                $recipients[$departmentCode][] = $memberEmail;
            }
        }

        foreach ($recipients as $departmentCode => $emails) {
            if (!\array_key_exists($departmentCode, $historiesByDepartment)) {
                $this->io->note(\sprintf(
                    'Manager of department code "%s" did not match any history during aggregation. Skipping...',
                    $departmentCode
                ));

                continue;
            }

            $this->io->text(\sprintf(
                'Will notify %d manager(s) of department %s about %d new declared mandate historie(s)',
                \count($emails),
                $departmentCode,
                \count($historiesByDepartment[$departmentCode])
            ));

            $this->declaredMandateHistoryNotifier->notifyAdherents($emails, $historiesByDepartment[$departmentCode]);
        }
    }

    /**
     * @param array|DeclaredMandateHistory[] $histories
     */
    private function markHistoriesAsNotified(array $histories): void
    {
        $this->io->text(\sprintf('Will mark %d new declared mandate histories as notified', \count($histories)));
        $this->io->progressStart(\count($histories));

        foreach ($histories as $declaredMandateHistory) {
            $declaredMandateHistory->setNotifiedAt(new \DateTimeImmutable());

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    private function getDepartment(Adherent $adherent): ?Zone
    {
        $departments = $adherent->getZonesOfType(Zone::DEPARTMENT, true);

        foreach ($departments as $department) {
            if (\in_array($department->getCode(), self::DEPARTMENT_CODE_EXCEPTIONS)) {
                return $department;
            }
        }

        return !empty($departments) ? reset($departments) : null;
    }
}
