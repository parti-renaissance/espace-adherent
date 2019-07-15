<?php

namespace AppBundle\Command;

use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportElectedRepresentativesRegisterCommand extends Command
{
    protected static $defaultName = 'app:elected-representatives-register:import';

    private const BATCH_SIZE = 100;

    private const COLUMNS = [
        'department_id' => ParameterType::INTEGER,
        'commune_id' => ParameterType::INTEGER,
        'type_elu' => ParameterType::STRING,
        'dpt' => ParameterType::STRING,
        'dpt_nom' => ParameterType::STRING,
        'nom' => ParameterType::STRING,
        'prenom' => ParameterType::STRING,
        'genre' => ParameterType::STRING,
        'date_naissance' => ParameterType::STRING,
        'code_profession' => ParameterType::INTEGER,
        'nom_profession' => ParameterType::STRING,
        'date_debut_mandat' => ParameterType::STRING,
        'nom_fonction' => ParameterType::STRING,
        'date_debut_fonction' => ParameterType::STRING,
        'nuance_politique' => ParameterType::STRING,
        'identification_elu' => ParameterType::INTEGER,
        'nationalite_elu' => ParameterType::STRING,
        'epci_siren' => ParameterType::INTEGER,
        'epci_nom' => ParameterType::STRING,
        'commune_dpt' => ParameterType::INTEGER,
        'commune_code' => ParameterType::INTEGER,
        'commune_nom' => ParameterType::STRING,
        'commune_population' => ParameterType::INTEGER,
        'canton_code' => ParameterType::INTEGER,
        'canton_nom' => ParameterType::STRING,
        'region_code' => ParameterType::STRING,
        'region_nom' => ParameterType::STRING,
        'euro_code' => ParameterType::INTEGER,
        'euro_nom' => ParameterType::STRING,
        'circo_legis_code' => ParameterType::INTEGER,
        'circo_legis_nom' => ParameterType::STRING,
        'infos_supp' => ParameterType::STRING,
        'uuid' => ParameterType::STRING,
        'nb_participation_events' => ParameterType::INTEGER,
    ];

    private $csvSeparator;

    /** @var SymfonyStyle */
    private $io;

    private $manager;
    private $skippedColumns = [];

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file of all elected to load')
            ->addOption('separator', 's', InputOption::VALUE_REQUIRED, 'CSV separator', ';')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getArgument('file');
        $this->csvSeparator = $input->getOption('separator');

        if (!$this->io->confirm('Clear all data on table ?', false)) {
            $this->io->success('import canceled');

            return 0;
        }

        $this->truncateTable();

        $this->io->success('Start importing Elected');

        $linesLoaded = $this->import($filePath);

        $this->io->text("$linesLoaded lines are loaded");
        $this->io->success('Done');

        return 0;
    }

    private function import(string $filePath): int
    {
        $this->io->progressStart();

        $file = fopen($filePath, 'rb');

        $headersRow = fgetcsv($file, 0, $this->csvSeparator);
        $this->buildQueryArguments($headersRow);

        $line = 0;
        $parameters = [];

        while (false !== ($data = fgetcsv($file, 0, $this->csvSeparator))) {
            ++$line;
            $tmp = [];
            foreach ($data as $index => $value) {
                if (\in_array($index, $this->skippedColumns)) {
                    continue;
                }
                $tmp[] = ('' === $value || 'NULL' === $value) ? null : $value;
            }
            $parameters[] = $tmp;

            if (0 === ($line % self::BATCH_SIZE)) {
                $this->load($line, $parameters);
                $parameters = [];
                $this->io->progressAdvance(self::BATCH_SIZE);
            }
        }

        if (!empty($parameters)) {
            $this->load($line, $parameters);
        }
        $this->io->progressFinish();

        return $line;
    }

    private function buildQueryArguments(array $headersRow): void
    {
        foreach ($headersRow as $index => $column) {
            $column = str_replace('"', '', $column);

            if (!\array_key_exists($column, self::COLUMNS)) {
                $this->skippedColumns[] = $index;
                $this->io->warning('column '.$column.' will be skipped');
                continue;
            }
        }
    }

    private function getUpdateQuery(int $start, int $end): string
    {
        return 'UPDATE elected_representatives_register e
JOIN adherents a on e.nom = a.last_name AND e.prenom = a.first_name AND e.date_naissance = a.birthdate
SET e.adherent_id = a.id, e.adherent_uuid = a.uuid
WHERE e.id BETWEEN '.$start.' AND '.$end;
    }

    private function load(int $line, array $parameters): void
    {
        $this->manager->getConnection()->executeUpdate(
            'INSERT INTO `elected_representatives_register` ('.implode(',', array_keys(self::COLUMNS)).') VALUES '
            .implode(',', array_map(function (array $row): string {
                return sprintf('(%s)', implode(',', fill_array(0, \count($row), '?')));
            }, $parameters)),
            array_merge(...$parameters)
        );
        $this->manager->getConnection()->exec($this->getUpdateQuery(max(1, $line - self::BATCH_SIZE), $line));
    }

    private function truncateTable(): void
    {
        $this->manager->getConnection()->executeQuery('TRUNCATE TABLE `elected_representatives_register`');
    }
}
