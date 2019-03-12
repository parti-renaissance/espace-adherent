<?php

namespace AppBundle\Command;

use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
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
    private $insertQuery;
    private $sqlValues;
    private $typesColumns;

    /** @var SymfonyStyle */
    private $io;

    private $manager;

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
        $filename = $input->getArgument('file');
        $this->csvSeparator = $input->getOption('separator');

        if (!is_readable($filename)) {
            throw new \InvalidArgumentException("file $filename not readable");
        }

        if (!$this->io->confirm('Clear all data on table ?', false)) {
            $this->io->success('import canceled');

            return 0;
        }

        $this->truncateTable();

        $this->io->success('Start importing Elected');

        $linesLoaded = $this->loadFile($filename);

        $this->io->text("$linesLoaded lines are loaded");
        $this->io->success('Done');

        return 0;
    }

    private function loadFile(string $filename): int
    {
        $this->io->progressStart();

        $file = fopen($filename, 'r');

        $columns = fgetcsv($file, 0, $this->csvSeparator);
        $this->buildQueryArguments($columns);

        $line = 0;
        $types = [];
        $parameters = [];
        $sqlValues = [];

        while (false !== ($data = fgetcsv($file, 0, $this->csvSeparator))) {
            ++$line;
            $types = array_merge($types, $this->typesColumns);

            foreach ($data as $value) {
                $parameters[] = '' === $value ? null : $value;
            }

            $sqlValues[] = $this->sqlValues;

            if (0 === ($line % self::BATCH_SIZE)) {
                $this->load($line, $sqlValues, $parameters, $types);
                $types = [];
                $parameters = [];
                $sqlValues = [];
                $this->io->progressAdvance(self::BATCH_SIZE);
            }
        }

        if (!empty($parameters)) {
            $this->load($line, $sqlValues, $parameters, $types);
        }
        $this->io->progressFinish();

        return $line;
    }

    private function buildQueryArguments(array $columns): void
    {
        $this->insertQuery = $this->getQuery($columns);

        $prepareValues = [];
        foreach ($columns as $column) {
            if (!\array_key_exists($column, self::COLUMNS)) {
                throw new \RuntimeException("Column $column could not be use");
            }

            $this->typesColumns[] = self::COLUMNS[$column];
            $prepareValues[] = '?';
        }

        $this->sqlValues = '('.implode(',', $prepareValues).')';
    }

    private function getQuery(array $columns): string
    {
        return 'INSERT INTO `elected_representatives_register` ('.implode(',', $columns).')';
    }

    private function getUpdateQuery(int $start, int $end): string
    {
        return 'UPDATE elected_representatives_register e
JOIN adherents a on e.nom = a.last_name AND e.prenom = a.first_name AND e.date_naissance = a.birthdate
SET e.adherent_id = a.id, e.adherent_uuid = a.uuid
WHERE e.id BETWEEN '.$start.' AND '.$end;
    }

    private function load(int $line, array $sqlValues, array $parameters, array $types)
    {
        $this->manager->getConnection()->executeUpdate(
            $this->insertQuery.' VALUES '.implode(',', $sqlValues),
            $parameters,
            $types
        );
        $this->manager->getConnection()->exec($this->getUpdateQuery(max(1, $line - self::BATCH_SIZE), $line));
    }

    private function truncateTable(): void
    {
        $this->manager->getConnection()->prepare('TRUNCATE TABLE `elected_representatives_register`')->execute();
    }
}
