<?php

namespace App\Command;

use App\Entity\VotePlace;
use App\VotePlace\VotePlaceFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class ImportVotePlacesCommand extends Command
{
    private const BATCH_SIZE = 1000;

    private $em;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:import:vote-places')
            ->addArgument('fileUrl', InputArgument::REQUIRED)
            ->setDescription('Import vote places from file store in Google Storage')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $rows = $this->parseCSV($input->getArgument('fileUrl'));
        } catch (FileNotFoundException $exception) {
            $this->io->error(sprintf('%s file not found', $input->getArgument('fileUrl')));

            return 1;
        }

        $this->em->beginTransaction();

        $this->createAndPersistVotePlaces($rows);

        $this->io->text('Vote places are loaded');
        $this->io->success('Done');
    }

    private function parseCSV(string $filename): array
    {
        $rows = [];
        if (false === ($handle = fopen($filename, 'r'))) {
            throw new FileNotFoundException(sprintf('% not found', $filename));
        }

        while (false !== ($data = fgetcsv($handle, 0, ';'))) {
            $row = array_map('trim', $data);

            if ('code_postal' === $row[4]) {
                continue;
            }

            $rows[] = [
                'code_insee_departement' => $row[2],
                'code_postal' => $row[4],
                'nom_commune_bdv' => $row[6],
                'code_bdv' => $row[8],
                'nom_bdv' => $row[9],
                'adresse_bdv' => $row[10],
            ];
        }

        fclose($handle);

        return $rows;
    }

    private function createAndPersistVotePlaces(array $rows): void
    {
        $batch = 0;
        $votePlaceRepository = $this->em->getRepository(VotePlace::class);

        $this->io->success('Start importing Vote places');

        $this->io->progressStart(\count($rows));

        foreach ($rows as $row) {
            if ($votePlaceRepository->findOneByCode($row['code_bdv'])) {
                continue;
            }

            $votePlace = VotePlaceFactory::createFromArray([
                    'name' => $row['nom_bdv'],
                    'code' => $row['code_bdv'],
                    'postalCode' => $this->formatPostalCode($row['code_postal']),
                    'city' => $row['nom_commune_bdv'],
                    'address' => $row['adresse_bdv'],
                ]
            );

            $this->em->persist($votePlace);

            ++$batch;

            if (0 === $batch % self::BATCH_SIZE) {
                $this->io->progressAdvance(self::BATCH_SIZE);

                $this->em->flush();
                $this->em->clear();
            }
        }

        $this->io->progressFinish();

        $this->em->flush();
        $this->em->commit();
    }

    private function formatPostalCode(string $postalCode): ?string
    {
        if (false !== strpos($postalCode, '/')) {
            return str_replace('/', ',', $postalCode);
        }

        if (is_numeric($postalCode)) {
            return $postalCode;
        }

        return null;
    }

    private function formatCountry(string $country): string
    {
        if (is_numeric($country) || '2A' === $country || '2B' === $country) {
            return 'FR';
        }

        return $country;
    }
}
