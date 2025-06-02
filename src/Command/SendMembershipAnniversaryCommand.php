<?php

namespace App\Command;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use App\Membership\MembershipNotifier;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Number of adherents per batch', 500)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $batchSize = (int) $input->getOption('batch-size');

        $this->io->title('Anniversary Recotisation Reminder');
        $this->io->text('Looking for adherents who contributed exactly one year ago and are not up to date.');

        $paginator = $this->getQueryBuilder();
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
    private function getQueryBuilder(): Paginator
    {
        $today = new \DateTimeImmutable('today');
        $targetDate = $today->modify('-1 year');
        $upToDateTag = TagEnum::getAdherentYearTag((int) $today->format('Y'));

        $qb = $this->adherentRepository->createQueryBuilder('a')
            ->andWhere('DATE(a.lastMembershipDonation) = :last_membership_date')
            ->andWhere('a.status = :status_enabled')
            ->andWhere('a.tags NOT LIKE :excluded_tag')
            ->setParameters([
                'last_membership_date' => $targetDate->format('Y-m-d'),
                'status_enabled' => Adherent::ENABLED,
                'excluded_tag' => '%'.$upToDateTag.'%',
            ])
        ;

        return new Paginator($qb->getQuery());
    }
}
