<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Entity\Reporting\EmailSubscriptionHistory;
use App\Entity\Reporting\EmailSubscriptionHistoryAction;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InitializeEmailSubscriptionHistoryCommand extends Command
{
    private const BATCH_SIZE = 5000;

    protected static $defaultName = 'app:adherent:initialize-email-subscriptions-history';

    /**
     * @var SymfonyStyle
     */
    private $io;
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create email subscriptions history. The history will be created for both users and adherents even inactives.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->isAlreadyInitialize()) {
            $this->io->error('Cannot initialize email subscription history. It already exists.');

            return 1;
        }

        $this->io->title('Starting email subscription history initialization.');

        $historyQuery = <<<'SQL'
INSERT INTO adherent_email_subscription_histories (adherent_uuid, subscribed_email_type, action, date) 
VALUES (:adherent_uuid, :subscribed_email_type, :action, :date)
SQL;
        $historyVsTagQuery = <<<'SQL'
INSERT INTO adherent_email_subscription_history_referent_tag (email_subscription_history_id, referent_tag_id) 
VALUES (:email_subscription_history_id, :referent_tag_id)
SQL;

        $connection = $this->em->getConnection();
        $connection->getConfiguration()->setSQLLogger(null); // Like this we are sure nothing is logged. Memory usage stays stable and it speeds things up.
        $progressBar = new ProgressBar($output, $this->getAdherentCount());

        $i = 0;
        foreach ($this->getAdherents() as $result) {
            /* @var Adherent $adherent */
            $adherent = $result[0];

            if (!$connection->isTransactionActive()) {
                $connection->beginTransaction();
            }

            /** @var Adherent $adherent */
            foreach ($adherent->getSubscriptionTypeCodes() as $subscription) {
                $connection->executeQuery(
                    $historyQuery,
                    [
                        'adherent_uuid' => $adherent->getUuid(),
                        'subscribed_email_type' => $subscription,
                        'action' => EmailSubscriptionHistoryAction::SUBSCRIBE,
                        'date' => $adherent->getActivatedAt() ? $adherent->getActivatedAt()->format('Y-m-d H:i:s') : $adherent->getRegisteredAt()->format('Y-m-d H:i:s'),
                    ]
                );
                $idHistory = $connection->lastInsertId();

                foreach ($adherent->getReferentTags() as $tag) {
                    $connection->executeQuery(
                        $historyVsTagQuery,
                        [
                            'email_subscription_history_id' => $idHistory,
                            'referent_tag_id' => $tag->getId(),
                        ]
                    );
                }
            }

            if (0 === (++$i % self::BATCH_SIZE)) {
                $connection->commit();
                $this->em->clear();
            }

            $progressBar->advance();
        }

        if ($connection->isTransactionActive()) {
            $connection->commit();
        }
        $progressBar->finish();

        $this->io->newLine(2);
        $this->io->success('Email subscription history initialized successfully!');
    }

    private function isAlreadyInitialize(): bool
    {
        $nbHistories = $this
            ->em
            ->getRepository(EmailSubscriptionHistory::class)
            ->createQueryBuilder('history')
            ->select('COUNT(history)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $nbHistories > 0;
    }

    private function getAdherents(): IterableResult
    {
        return $this
            ->createQueryBuilder()
            ->getQuery()
            ->iterate()
        ;
    }

    private function getAdherentCount(): int
    {
        return $this
            ->createQueryBuilder()
            ->select('COUNT(adherent)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function createQueryBuilder(): QueryBuilder
    {
        return $this
            ->em
            ->getRepository(Adherent::class)
            ->createQueryBuilder('adherent')
        ;
    }
}
