<?php

declare(strict_types=1);

namespace App\Command;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use App\Entity\UserActionHistory;
use App\History\UserActionHistoryHandler;
use App\History\UserActionHistoryTypeEnum;
use App\Membership\MembershipNotifier;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:membership:anniversary-reminder',
    description: 'Send anniversary recotisation email to adherents who contributed one year ago and are not up to date',
)]
class SendMembershipAnniversaryCommand extends Command
{
    private ?SymfonyStyle $io = null;

    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly EntityManagerInterface $em,
        private readonly MembershipNotifier $membershipNotifier,
        private readonly UserActionHistoryHandler $userActionHistoryHandler,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Number of adherents per batch', 500)
            ->addOption('date', null, InputOption::VALUE_OPTIONAL, 'Override the membership donation date to match (format: Y-m-d)')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $batchSize = (int) $input->getOption('batch-size');
        $dateOption = $input->getOption('date');

        if (null !== $dateOption) {
            try {
                $targetDate = new \DateTimeImmutable($dateOption);
            } catch (\Exception $e) {
                $this->io->error("Invalid date format. Please use 'Y-m-d'.");

                return Command::FAILURE;
            }
        } else {
            $targetDate = new \DateTimeImmutable('today')->modify('-1 year');
        }

        $this->io->title('Anniversary Recotisation Reminder');
        $this->io->text(\sprintf('Target date for membership donation match: %s', $targetDate->format('Y-m-d')));

        $paginator = $this->getQueryBuilder($targetDate);
        $total = $paginator->count();

        $this->io->text("Found $total adherents to remind.");

        if (0 === $total) {
            return Command::SUCCESS;
        }

        $this->io->progressStart($total);

        $offset = 0;
        $paginator->getQuery()->setMaxResults($batchSize);

        do {
            foreach ($paginator as $adherent) {
                $this->membershipNotifier->sendMembershipAnniversaryReminder($adherent);
                $this->userActionHistoryHandler->createMembershipAnniversaryReminded($adherent);

                $this->io->progressAdvance();
                ++$offset;
            }

            $paginator->getQuery()->setFirstResult($offset);
            $this->em->clear();
        } while ($offset < $total);

        $this->io->progressFinish();
        $this->io->success('Done.');

        return Command::SUCCESS;
    }

    /**
     * @return Paginator|Adherent[]
     */
    private function getQueryBuilder(\DateTimeImmutable $targetDate): Paginator
    {
        $today = new \DateTimeImmutable('today');
        $currentYear = (int) $today->format('Y');
        $upToDateTag = TagEnum::getAdherentYearTag($currentYear);

        $qb = $this->adherentRepository->createQueryBuilder('a');

        $qb
            ->leftJoin(
                UserActionHistory::class,
                'uah',
                Join::WITH,
                $qb->expr()->andX(
                    'uah.adherent = a',
                    'uah.type = :history_type',
                    'YEAR(uah.date) = :current_year'
                )
            )
            ->andWhere('uah.id IS NULL')
            ->andWhere('DATE(a.lastMembershipDonation) = :last_membership_date')
            ->andWhere('a.status = :status_enabled')
            ->andWhere('a.tags NOT LIKE :excluded_tag')
            ->setParameters([
                'last_membership_date' => $targetDate->format('Y-m-d'),
                'status_enabled' => Adherent::ENABLED,
                'excluded_tag' => '%'.$upToDateTag.'%',
                'history_type' => UserActionHistoryTypeEnum::MEMBERSHIP_ANNIVERSARY_REMINDED,
                'current_year' => $currentYear,
            ])
        ;

        return new Paginator($qb->getQuery());
    }
}
