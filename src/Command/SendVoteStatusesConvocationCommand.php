<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Mailer\MailerService;
use App\Mailer\Message\VoteStatusesConvocationMessage;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendVoteStatusesConvocationCommand extends Command
{
    protected static $defaultName = 'app:vote-statuses:send-convocation';

    private AdherentRepository $adherentRepository;
    private EntityManagerInterface $entityManager;
    private SymfonyStyle $io;
    private MailerService $transactionalMailer;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        AdherentRepository $adherentRepository,
        EntityManagerInterface $entityManager,
        MailerService $transactionalMailer,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->adherentRepository = $adherentRepository;
        $this->entityManager = $entityManager;
        $this->transactionalMailer = $transactionalMailer;
        $this->urlGenerator = $urlGenerator;

        parent::__construct();
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function configure()
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED)
            ->addOption('emails', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
            ->addArgument('convocation-date', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = (int) $input->getOption('limit');
        $selectedEmails = $input->getOption('emails');
        $convocationDate = new \DateTime($input->getArgument('convocation-date'));

        if (!$count = $this->countAdherents($convocationDate, $selectedEmails)) {
            $this->io->note('0 adherent to notify');

            return 0;
        }

        $total = $limit && $limit < $count ? $limit : $count;
        $chunkLimit = $limit && $limit < 500 ? $limit : 500;

        if (false === $this->io->confirm(sprintf('Are you sure to notify %d adherents ?', $total), false)) {
            return 1;
        }

        $this->io->progressStart($total);
        $alreadySentCount = 0;

        $certificationUrl = $this->urlGenerator->generate('app_certification_request_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $now = new \DateTime();

        while (
            $alreadySentCount < $total
            && ($adherents = $this->getChunkAdherents($convocationDate, $selectedEmails, $chunkLimit))
        ) {
            if ($this->transactionalMailer->sendMessage(VoteStatusesConvocationMessage::create($adherents, $certificationUrl))) {
                if (!$selectedEmails) {
                    array_walk($adherents, function (Adherent $adherent) use ($now) {
                        $adherent->voteStatusesConvocationSentAt = $now;
                    });
                }

                $alreadySentCount += $chunkCount = \count($adherents);

                $this->entityManager->flush();
                $this->entityManager->clear();

                $this->io->progressAdvance($chunkCount);
            } else {
                $this->io->error('Error when sending the email');

                return 2;
            }
        }

        $this->io->progressFinish();

        return 0;
    }

    private function countAdherents(\DateTime $convocationDate, array $emails): int
    {
        return (int) $this->getQueryBuilder($convocationDate, $emails)
            ->select('COUNT(adherent.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /** @return Adherent[] */
    private function getChunkAdherents(\DateTime $convocationDate, array $emails, int $limit = 500): array
    {
        return $this->getQueryBuilder($convocationDate, $emails)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Paginator|Adherent[]
     */
    private function getQueryBuilder(\DateTime $convocationDate, array $emails): QueryBuilder
    {
        $queryBuilder = $this->adherentRepository
            ->createQueryBuilder('adherent')
            ->select('PARTIAL adherent.{id, firstName, lastName, emailAddress, voteStatusesConvocationSentAt}')
            ->where('adherent.status = :status AND adherent.adherent = true AND adherent.source IS NULL')
            ->andWhere('adherent.activatedAt IS NOT NULL AND adherent.activatedAt <= :date')
            ->setParameter('status', Adherent::ENABLED)
            ->setParameter('date', (clone $convocationDate)->modify('-3 months'))
        ;

        if ($emails) {
            $queryBuilder
                ->andWhere('adherent.emailAddress IN (:emails)')
                ->setParameter('emails', $emails)
            ;
        } else {
            $queryBuilder->andWhere('adherent.voteStatusesConvocationSentAt IS NULL');
        }

        return $queryBuilder;
    }
}
