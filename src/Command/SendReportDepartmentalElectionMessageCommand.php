<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\DepartmentalElectionReportMessage;
use App\Membership\MembershipSourceEnum;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:departmental-election:send-report-message',
)]
class SendReportDepartmentalElectionMessageCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerService $transactionalMailer,
        ) {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED)
            ->addOption('emails', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
            ->addOption('dpt-code', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
            ->addOption('sent-until', null, InputOption::VALUE_REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getOption('limit');
        $selectedEmails = $input->getOption('emails');
        $dptCodes = $input->getOption('dpt-code');

        if ($sentUntil = $input->getOption('sent-until')) {
            $sentUntil = new \DateTime($sentUntil);
        } else {
            $sentUntil = new \DateTime();
        }

        if (!$count = $this->countAdherents($selectedEmails, $dptCodes, $sentUntil)) {
            $this->io->note('0 adherent to notify');

            return self::SUCCESS;
        }

        $total = $limit && $limit < $count ? $limit : $count;
        $chunkLimit = $limit && $limit < 500 ? $limit : 500;

        if (false === $this->io->confirm(sprintf('Are you sure to notify %d adherents ?', $total), false)) {
            return self::FAILURE;
        }

        $this->io->progressStart($total);
        $alreadySentCount = 0;

        $now = new \DateTime();

        while (
            $alreadySentCount < $total
            && ($adherents = $this->getChunkAdherents($selectedEmails, $dptCodes, $sentUntil, $chunkLimit))
        ) {
            if ($this->transactionalMailer->sendMessage(DepartmentalElectionReportMessage::create($adherents))) {
                if (!$selectedEmails) {
                    array_walk($adherents, function (Adherent $adherent) use ($now) {
                        $adherent->globalNotificationSentAt = $now;
                    });
                }

                $alreadySentCount += $chunkCount = \count($adherents);

                $this->entityManager->flush();
                $this->entityManager->clear();

                $this->io->progressAdvance($chunkCount);
            } else {
                $this->io->error('Error when sending the email');

                return self::FAILURE;
            }
        }

        $this->io->progressFinish();

        return self::SUCCESS;
    }

    private function countAdherents(array $emails, array $dptCodes, \DateTime $sentUntil): int
    {
        return (int) $this->getQueryBuilder($emails, $dptCodes, $sentUntil)
            ->select('COUNT(adherent.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /** @return Adherent[] */
    private function getChunkAdherents(array $emails, array $dptCodes, \DateTime $sentUntil, int $limit = 500): array
    {
        return $this->getQueryBuilder($emails, $dptCodes, $sentUntil)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Paginator|Adherent[]
     */
    private function getQueryBuilder(array $emails, array $dptCodes, \DateTime $sentUntil): QueryBuilder
    {
        $queryBuilder = $this->adherentRepository
            ->createQueryBuilder('adherent')
            ->select('PARTIAL adherent.{id, firstName, lastName, emailAddress, globalNotificationSentAt}')
            ->where('adherent.status = :status AND adherent.adherent = true')
            ->andWhere('adherent.source = :renaissance_source AND adherent.lastMembershipDonation IS NOT NULL')
            ->andWhere('adherent.activatedAt IS NOT NULL')
            ->setParameter('status', Adherent::ENABLED)
            ->setParameter('renaissance_source', MembershipSourceEnum::RENAISSANCE)
        ;

        if ($emails) {
            $queryBuilder
                ->andWhere('adherent.emailAddress IN (:emails)')
                ->setParameter('emails', $emails)
            ;
        } else {
            $queryBuilder
                ->andWhere('adherent.globalNotificationSentAt IS NULL OR adherent.globalNotificationSentAt < :sent_until')
                ->andWhere('adherent.postAddress.country = \'FR\' AND LEFT(adherent.postAddress.postalCode, 2) IN (:dpt_codes)')
                ->setParameter('sent_until', $sentUntil)
                ->setParameter('dpt_codes', $dptCodes)
            ;
        }

        return $queryBuilder;
    }
}
