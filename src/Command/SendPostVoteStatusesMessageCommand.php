<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Mailer\MailerService;
use App\Mailer\Message\PostVoteStatusesMessage;
use App\Membership\MembershipSourceEnum;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendPostVoteStatusesMessageCommand extends Command
{
    protected static $defaultName = 'app:vote-statuses:send-post-message';

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
            ->addOption('created-after', null, InputOption::VALUE_REQUIRED, '', '2022-09-17 20:00:00')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = (int) $input->getOption('limit');
        $selectedEmails = $input->getOption('emails');
        $createdAfter = $input->getOption('created-after');

        if (!$count = $this->countAdherents($selectedEmails, $createdAfter)) {
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

        $renaissanceAdhesionUrl = $this->urlGenerator->generate('app_renaissance_adhesion', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $now = new \DateTime();

        while (
            $alreadySentCount < $total
            && ($adherents = $this->getChunkAdherents($selectedEmails, $createdAfter, $chunkLimit))
        ) {
            if ($this->transactionalMailer->sendMessage(PostVoteStatusesMessage::create($adherents, $renaissanceAdhesionUrl))) {
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

                return 2;
            }
        }

        $this->io->progressFinish();

        return 0;
    }

    private function countAdherents(array $emails, string $createdAfter): int
    {
        return (int) $this->getQueryBuilder($emails, $createdAfter)
            ->select('COUNT(adherent.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /** @return Adherent[] */
    private function getChunkAdherents(array $emails, string $createdAfter, int $limit = 500): array
    {
        return $this->getQueryBuilder($emails, $createdAfter)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Paginator|Adherent[]
     */
    private function getQueryBuilder(array $emails, string $createdAfter): QueryBuilder
    {
        $queryBuilder = $this->adherentRepository
            ->createQueryBuilder('adherent')
            ->select('PARTIAL adherent.{id, firstName, lastName, emailAddress, globalNotificationSentAt}')
            ->where('adherent.status = :status AND adherent.adherent = true')
            ->andWhere('adherent.source IS NULL OR (adherent.source = :renaissance_source AND adherent.activatedAt > :created_after)')
            ->andWhere('adherent.activatedAt IS NOT NULL')
            ->setParameter('status', Adherent::ENABLED)
            ->setParameter('renaissance_source', MembershipSourceEnum::RENAISSANCE)
            ->setParameter('created_after', \DateTime::createFromFormat('Y-m-d H:i:s', $createdAfter))
        ;

        if ($emails) {
            $queryBuilder
                ->andWhere('adherent.emailAddress IN (:emails)')
                ->setParameter('emails', $emails)
            ;
        } else {
            $queryBuilder->andWhere('adherent.globalNotificationSentAt IS NULL');
        }

        return $queryBuilder;
    }
}
