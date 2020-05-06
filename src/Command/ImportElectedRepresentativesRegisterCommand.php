<?php

namespace App\Command;

use App\Entity\ElectedRepresentativesRegister;
use App\Repository\ElectedRepresentativesRegisterRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportElectedRepresentativesRegisterCommand extends Command
{
    protected static $defaultName = 'app:elected-representatives-register:import';

    private const BATCH_SIZE = 1000;

    /** @var SymfonyStyle */
    private $io;

    private $manager;
    private $electedRepresentativesRepository;
    private $storage;

    public function __construct(
        EntityManagerInterface $manager,
        ElectedRepresentativesRegisterRepository $electedRepresentativesRepository,
        Filesystem $storage
    ) {
        $this->manager = $manager;
        $this->electedRepresentativesRepository = $electedRepresentativesRepository;
        $this->storage = $storage;

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

        if (!$this->io->confirm('Clear all data on table ?', false)) {
            $this->io->success('import canceled');

            return 0;
        }

        $this->manager->beginTransaction();

        try {
            $this->deleteRecords();

            $this->io->title('Start importing Elected representatives');

            $this->import($filePath);

            $this->manager->commit();
        } catch (\Exception $exception) {
            $this->manager->rollback();

            throw $exception;
        }

        $this->io->success('Done');

        return 0;
    }

    private function import(string $filePath): void
    {
        $csv = Reader::createFromStream($this->storage->readStream($filePath));
        $csv->setHeaderOffset(0);

        $this->io->progressStart($total = $csv->count());

        $line = 0;
        foreach ($csv as $row) {
            $birthDate = new \DateTime($row['date_naissance']);

            $electedRepresentative = ElectedRepresentativesRegister::create(
                (int) $row['department_id'] ?? null,
                (int) $row['commune_id'] ?? null,
                null,
                $row['type_elu'],
                $row['dpt'],
                $row['dpt_nom'],
                $row['nom'],
                $row['prenom'],
                $row['genre'],
                $birthDate,
                (int) $row['code_profession'] ?? null,
                $row['nom_profession'],
                $row['date_debut_mandat'],
                $row['nom_fonction'],
                new \DateTime($row['date_debut_fonction']),
                $row['nuance_politique'],
                (int) $row['identification_elu'] ?? null,
                $row['nationalite_elu'],
                (int) $row['epci_siren'] ?? null,
                $row['epci_nom'],
                (int) $row['commune_dpt'] ?? null,
                (int) $row['commune_code'] ?? null,
                $row['commune_nom'],
                (int) $row['commune_population'] ?? null,
                (int) $row['canton_code'] ?? null,
                $row['canton_nom'],
                $row['region_code'],
                $row['region_nom'],
                (int) $row['euro_code'] ?? null,
                $row['euro_nom'],
                (int) $row['circo_legis_code'] ?? null,
                $row['circo_legis_nom'],
                $row['infos_supp'],
                $row['uuid'],
                (int) $row['nb_participation_events'] ?? null,
                null
            );

            $this->manager->persist($electedRepresentative);

            $this->io->progressAdvance();
            ++$line;

            if (0 === ($line % self::BATCH_SIZE)) {
                $this->manager->flush();
                $this->manager->clear();
            }
        }

        $this->manager->flush();
        $this->manager->clear();

        $this->io->progressFinish();

        $this->io->text("$line lines are loaded");
    }

    private function deleteRecords(): void
    {
        $this->electedRepresentativesRepository->createQueryBuilder('e')->delete()->getQuery()->execute();
    }
}
