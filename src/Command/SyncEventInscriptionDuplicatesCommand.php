<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\NationalEvent\EventInscription;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:sync-event-inscription-duplicates',
    description: 'Met à jour les inscriptions principales avec les infos du dernier doublon.'
)]
class SyncEventInscriptionDuplicatesCommand extends Command
{
    private const BATCH_SIZE = 500;

    public function __construct(
        private EntityManagerInterface $em,
        private EventInscriptionRepository $inscriptionRepository,
        private Connection $connection,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRun = $input->getOption('dry-run');
        $offset = 0;
        $totalUpdated = 0;

        while (true) {
            $sql = '
                SELECT event_id, address_email, first_name, last_name, COUNT(1) AS total
                FROM national_event_inscription
                GROUP BY event_id, address_email, first_name, last_name
                HAVING total > 1
                ORDER BY event_id, address_email
                LIMIT '.self::BATCH_SIZE." OFFSET $offset
            ";

            $result = $this->connection->executeQuery($sql);
            $quadruplets = $result->fetchAllAssociative();

            if (empty($quadruplets)) {
                break;
            }

            foreach ($quadruplets as $q) {
                $inscriptions = $this->inscriptionRepository
                    ->createQueryBuilder('e')
                    ->where('e.event = :eventId')
                    ->andWhere('e.addressEmail = :email')
                    ->andWhere('e.firstName = :firstName')
                    ->andWhere('e.lastName = :lastName')
                    ->setParameters([
                        'eventId' => $q['event_id'],
                        'email' => $q['address_email'],
                        'firstName' => $q['first_name'],
                        'lastName' => $q['last_name'],
                    ])
                    ->getQuery()
                    ->getResult()
                ;

                $duplicates = array_filter($inscriptions, static fn (EventInscription $i) => $i->isDuplicate());
                $mainEntries = array_filter($inscriptions, static fn (EventInscription $i) => !$i->isDuplicate());
                $mainInscription = array_values($mainEntries)[0];

                usort($duplicates, fn ($a, $b) => $a->getCreatedAt() <=> $b->getCreatedAt());

                foreach ($duplicates as $duplicate) {
                    $mainInscription->updateFromDuplicate($duplicate);
                }

                ++$totalUpdated;
            }

            if (!$dryRun) {
                $this->em->flush();
            }

            $this->em->clear();
            $offset += self::BATCH_SIZE;
        }

        $output->writeln("$totalUpdated inscriptions principales mises à jour");

        return Command::SUCCESS;
    }
}
