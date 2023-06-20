<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\DepartmentalElectionFdeVoteInvitationMessage;
use App\Mailer\Message\Renaissance\DepartmentalElectionVoteInvitationMessage;
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
    name: 'app:departmental-election:send-vote-invitation',
)]
class SendVoteInvitationForDepartmentalElectionsCommand extends Command
{
    private const DPT_CODES = [
        '01',
        '02',
        '03',
        '04',
        '05',
        '06',
        '07',
        '08',
        '09',
        '10',
        '11',
        '12',
        '13',
        '14',
        '15',
        '16',
        '17',
        '18',
        '19',
        '21',
        '22',
        '23',
        '24',
        '25',
        '26',
        '27',
        '28',
        '29',
        '20',
        '30',
        '31',
        '32',
        '33',
        '34',
        '35',
        '36',
        '37',
        '38',
        '39',
        '40',
        '41',
        '42',
        '43',
        '44',
        '45',
        '46',
        '47',
        '48',
        '49',
        '50',
        '51',
        '52',
        '53',
        '54',
        '55',
        '56',
        '57',
        '58',
        '59',
        '60',
        '61',
        '62',
        '63',
        '64',
        '65',
        '66',
        '67',
        '68',
        '69',
        '70',
        '71',
        '72',
        '73',
        '74',
        '75',
        '76',
        '77',
        '78',
        '79',
        '80',
        '81',
        '82',
        '83',
        '84',
        '85',
        '86',
        '87',
        '88',
        '89',
        '90',
        '91',
        '92',
        '93',
        '94',
        '95',
    ];

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
            ->addOption('exclude-dpt-code', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
            ->addOption('sent-until', null, InputOption::VALUE_REQUIRED)
            ->addOption('fde-only', null, InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getOption('limit');
        $selectedEmails = $input->getOption('emails');
        $excludedDptCodes = $input->getOption('exclude-dpt-code');
        $fdeOnly = $input->getOption('fde-only');

        if ($sentUntil = $input->getOption('sent-until')) {
            $sentUntil = new \DateTime($sentUntil);
        } else {
            $sentUntil = new \DateTime();
        }

        if (!$count = $this->countAdherents($selectedEmails, $excludedDptCodes, $fdeOnly, $sentUntil)) {
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
            && ($adherents = $this->getChunkAdherents($selectedEmails, $excludedDptCodes, $fdeOnly, $sentUntil, $chunkLimit))
        ) {
            if ($this->transactionalMailer->sendMessage(
                $fdeOnly ?
                    DepartmentalElectionFdeVoteInvitationMessage::create($adherents) :
                    DepartmentalElectionVoteInvitationMessage::create($adherents)
            )) {
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

    private function countAdherents(array $emails, array $excludedDptCodes, bool $fdeOnly, \DateTime $sentUntil): int
    {
        return (int) $this->getQueryBuilder($emails, $excludedDptCodes, $fdeOnly, $sentUntil)
            ->select('COUNT(adherent.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /** @return Adherent[] */
    private function getChunkAdherents(
        array $emails,
        array $excludedDptCodes,
        bool $fdeOnly,
        \DateTime $sentUntil,
        int $limit = 500
    ): array {
        return $this->getQueryBuilder($emails, $excludedDptCodes, $fdeOnly, $sentUntil)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Paginator|Adherent[]
     */
    private function getQueryBuilder(
        array $emails,
        array $excludedDptCodes,
        bool $fdeOnly,
        \DateTime $sentUntil
    ): QueryBuilder {
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
                ->andWhere('adherent.postAddress.country NOT IN (:avoid_country_codes) OR (adherent.postAddress.country = \'FR\' AND LEFT(adherent.postAddress.postalCode, 3) NOT IN (:avoid_postal_codes))')
                ->setParameter('avoid_country_codes', [
                    'GP',
                    'MQ',
                    'GF',
                    'RE',
                    'YT',
                    'PF',
                    'NC',
                ])
                ->setParameter('avoid_postal_codes', [
                    '971',
                    '972',
                    '973',
                    '974',
                    '975',
                    '976',
                    '977',
                    '978',
                    '984',
                    '986',
                    '987',
                    '988',
                    '989',
                ])
                ->setParameter('sent_until', $sentUntil)
            ;

            if ($fdeOnly) {
                $queryBuilder->andWhere('adherent.postAddress.country != \'FR\'');
            } else {
                $queryBuilder
                    ->andWhere('adherent.postAddress.country != \'FR\' OR (adherent.postAddress.country = \'FR\' AND LEFT(adherent.postAddress.postalCode, 2) IN (:dpt_codes))')
                    ->setParameter('dpt_codes', array_diff(self::DPT_CODES, $excludedDptCodes))
                ;
            }
        }

        return $queryBuilder;
    }
}
