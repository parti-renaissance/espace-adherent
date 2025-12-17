<?php

declare(strict_types=1);

namespace App\Command;

use App\AdherentMessage\AdherentMessageStatusEnum;
use App\Mailchimp\Campaign\Report\Command\SyncReportCommand;
use App\Repository\AdherentMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

#[AsCommand(
    name: 'mailchimp:report:download',
    description: 'Download Mailchimp campaigns reports',
)]
class MailchimpReportDownloadCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdherentMessageRepository $adherentMessageRepository,
        private readonly MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('recent-only', null, InputOption::VALUE_NONE, 'Download campaign reports for only recent messages')
            ->addOption('recent-interval', null, InputOption::VALUE_REQUIRED, 'Duration of recent interval in day (default: 14 days)', 14)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Mailchimp report catch-up');

        $recentOnly = (bool) $input->getOption('recent-only');
        $recentDays = (int) $input->getOption('recent-interval');

        $from = null;
        if ($recentOnly) {
            $from = new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
                ->modify(\sprintf('-%d days', $recentDays))
            ;
        }

        $pageSize = 500;
        $qb = $this->createBaseQuery($from)
            ->orderBy('am.id', 'ASC')
            ->setMaxResults($pageSize)
        ;

        $q = $qb->getQuery();
        $paginator = new Paginator($q);
        $total = $paginator->count();

        $this->io->progressStart($total);

        $offset = 0;
        do {
            $q->setFirstResult($offset);

            foreach ($paginator->getIterator() as $am) {
                $this->io->progressAdvance();
                $this->messageBus->dispatch(new SyncReportCommand($am->getUuid(), autoReschedule: false), [new TransportNamesStamp('mailchimp_campaign_batch')]);
            }

            $this->entityManager->clear();

            $offset += $pageSize;
        } while ($offset < $total);

        $this->io->progressFinish();

        return self::SUCCESS;
    }

    private function createBaseQuery(?\DateTimeInterface $from): QueryBuilder
    {
        $qb = $this->adherentMessageRepository
            ->createQueryBuilder('am')
            ->where('am.status = :status AND am.isStatutory = :false')
            ->setParameter('status', AdherentMessageStatusEnum::SENT)
            ->setParameter('false', false)
        ;

        if ($from) {
            $qb->andWhere('am.sentAt >= :from')->setParameter('from', $from);
        }

        return $qb;
    }
}
