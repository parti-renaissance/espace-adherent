<?php

namespace AppBundle\Command;

use AppBundle\Doctrine\DBAL\BatchedConnection;
use AppBundle\Form\TypeExtension\TextTypeExtension;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FixTitleCaseCommand extends Command
{
    private $connection;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(Connection $connection)
    {
        parent::__construct();

        $this->connection = new BatchedConnection($connection);
    }

    protected function configure()
    {
        $this
            ->setName('app:fix-title-case')
            ->setDescription('Fix case of adherents, citizen projects, committees and events for various property')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->connection->setOnCommitCallback(function ($i) {
            $this->io->success("$i queries processed in total");
            usleep(500000); // 0.5s
        });

        $this->io->progressStart(4);
        $this->connection->startBatch();

        $this->io->title("\nFixing adherents case");
        $this->fixAdherentsCase();
        $this->io->progressAdvance();

        $this->io->title("\nFixing citizen projects case");
        $this->fixCitizenProjectsCase();
        $this->io->progressAdvance();

        $this->io->title("\nFixing committees case");
        $this->fixCommittesCase();
        $this->io->progressAdvance();

        $this->io->title("\nFixing events case");
        $this->fixEventsCase();

        $this->connection->endBatch();
        $this->io->progressFinish();
    }

    private function fixAdherentsCase(): void
    {
        foreach ($this->connection->executeQuery('SELECT id, first_name, last_name FROM adherents') as $adherent) {
            $this->connection->executeUpdate(
                'UPDATE adherents SET first_name = ?, last_name = ? WHERE id = ?',
                [
                    TextTypeExtension::formatIdentityCase($adherent['first_name']),
                    TextTypeExtension::formatIdentityCase($adherent['last_name']),
                    $adherent['id'],
                ],
                [
                    \PDO::PARAM_STR,
                    \PDO::PARAM_STR,
                    \PDO::PARAM_INT,
                ]
            );
        }
    }

    protected function fixCitizenProjectsCase(): void
    {
        foreach ($this->connection->executeQuery('SELECT id, `name`, subtitle FROM citizen_projects') as $project) {
            $this->connection->executeUpdate(
                'UPDATE citizen_projects SET `name` = ?, subtitle = ? WHERE id = ?',
                [
                    TextTypeExtension::formatTitleCase($project['name']),
                    TextTypeExtension::formatTitleCase($project['subtitle']),
                    $project['id'],
                ],
                [
                    \PDO::PARAM_STR,
                    \PDO::PARAM_STR,
                    \PDO::PARAM_INT,
                ]
            );
        }
    }

    protected function fixCommittesCase(): void
    {
        foreach ($this->connection->executeQuery('SELECT id, `name` FROM committees') as $committee) {
            $this->connection->executeUpdate(
                'UPDATE committees SET `name` = ? WHERE id = ?',
                [
                    TextTypeExtension::formatTitleCase($committee['name']),
                    $committee['id'],
                ],
                [
                    \PDO::PARAM_STR,
                    \PDO::PARAM_INT,
                ]
            );
        }
    }

    protected function fixEventsCase(): void
    {
        foreach ($this->connection->executeQuery('SELECT id, `name` FROM events') as $event) {
            $this->connection->executeUpdate(
                'UPDATE events SET `name` = ? WHERE id = ?',
                [
                    TextTypeExtension::formatTitleCase($event['name']),
                    $event['id'],
                ],
                [
                    \PDO::PARAM_STR,
                    \PDO::PARAM_INT,
                ]
            );
        }
    }
}
