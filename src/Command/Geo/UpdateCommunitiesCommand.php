<?php

declare(strict_types=1);

namespace App\Command\Geo;

use App\Command\Geo\Helper\Persister;
use App\Command\Geo\Helper\Summary;
use App\Entity\Geo\City;
use App\Entity\Geo\CityCommunity;
use App\Entity\Geo\Department;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:geo:update-communities',
    description: 'Update french intercommunalities according to INSEE data',
)]
final class UpdateCommunitiesCommand extends Command
{
    private const SOURCE = 'https://www.insee.fr/fr/statistiques/fichier/2510634/Intercommunalite-Metropole_au_01-01-2020.zip';
    private const FILENAME = 'Intercommunalité - Métropole au 01-01-2020.xlsx';
    private const SHEET_NAME = 'Composition_communale';

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var Collection
     */
    private $entities;

    /**
     * @var array<string, Department>
     */
    private $departments;

    /**
     * @var array<string, City>
     */
    private $cities;

    public function __construct(HttpClientInterface $httpClient, EntityManagerInterface $em)
    {
        $this->httpClient = $httpClient;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Execute the algorithm but will not persist in database.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->entities = new ArrayCollection();
        $this->departments = $this->em->getRepository(Department::class)->findAllGroupedByCode();
        $this->cities = $this->em->getRepository(City::class)->findAllGroupedByCode();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Start updating french intercommunalities');

        $this->populateCommunitiesFromDatabase();
        $this->populateCitiesFromDatabase();
        $this->loadCommunitiesFromSource();

        $summary = new Summary($this->io);
        $summary->run($this->entities);

        $persister = new Persister($this->io, $this->em);
        $persister->run($this->entities, $input->getOption('dry-run'));

        return self::SUCCESS;
    }

    private function populateCommunitiesFromDatabase(): void
    {
        $this->io->section('Fetching communities and cities from database');
        $this->io->comment([
            'It marks entities as inactive, they become back active if they are found in the source',
            "It erases links to departments because we'll rebuild them in the following steps",
        ]);

        $communities = $this->em->getRepository(CityCommunity::class)->findAll();
        foreach ($communities as $community) {
            $key = CityCommunity::class.'#'.$community->getCode();
            $this->entities->set($key, $community);

            $community->clearDepartments();
            $community->activate(false);
        }
    }

    private function populateCitiesFromDatabase(): void
    {
        $this->io->section('Fetching cities from database');
        $this->io->comment("It erases links to city communities because we'll rebuild them in the following steps");

        /* @var City $city */
        foreach ($this->cities as $city) {
            $key = City::class.'#'.$city->getCode();
            $this->entities->set($key, $city);

            $city->setCityCommunity(null);
        }
    }

    private function loadCommunitiesFromSource(): void
    {
        $this->io->section('Loading communities from source');
        $this->io->comment(\sprintf('Fetching data from %s', self::SOURCE));

        $source = $this->openUrlAsFile(self::SOURCE, self::FILENAME);

        // Discard headers
        for ($i = 0; $i < 5; ++$i) {
            array_shift($source);
        }

        $header = array_shift($source);
        foreach ($source as $raw) {
            $row = array_combine($header, $raw);

            // Discards invalid entries
            if (!preg_match('/^\d+$/', $row['EPCI'])) {
                continue;
            }

            $row['DEP'] = str_pad($row['DEP'], 2, '0', \STR_PAD_LEFT);
            $row['CODGEO'] = str_pad($row['CODGEO'], 5, '0', \STR_PAD_LEFT);

            $department = $this->departments[$row['DEP']] ?? null;
            if (!$department) {
                throw new \RuntimeException(\sprintf('Department %s not found for community %s (%s)', $row['DEP'], $row['LIBEPCI'], $row['EPCI']));
            }

            $key = CityCommunity::class.'#'.$row['EPCI'];
            $community = $this->entities->get($key);
            if (!$community) {
                $community = new CityCommunity($row['EPCI'], $row['LIBEPCI']);
                $this->entities->set($key, $community);
            }

            // Updates general data
            $community->setName($row['LIBEPCI']);
            $community->addDepartment($department);

            // Activates entity, once it's found in the source
            $community->activate();

            // Links community to the city
            /* @var City|null $city */
            $city = $this->cities[$row['CODGEO']] ?? null;
            if (!$city) {
                $this->io->caution(\sprintf('City %s not found for community %s (%s)', $row['CODGEO'], $row['LIBEPCI'], $row['EPCI']));
            } else {
                $city->setCityCommunity($community);
            }
        }
    }

    private function openUrlAsFile(string $zipUrl, string $filename): array
    {
        $response = $this->httpClient->request('GET', $zipUrl);

        $dir = \sprintf('%s/%s', sys_get_temp_dir(), uniqid(md5($zipUrl), true));

        $zipFilename = $dir.'.zip';
        $xlsxFilename = $dir.'/'.$filename;

        file_put_contents($zipFilename, $response->getContent());

        $zip = new \ZipArchive();

        if (true !== ($code = $zip->open($zipFilename))) {
            throw new \RuntimeException(\sprintf('ZipArchive::open() error: %d', $code));
        }

        if (!$zip->extractTo($dir)) {
            throw new \RuntimeException('Error during extracting data from archive');
        }

        $zip->close();

        $reader = new Xlsx();
        $spreadsheet = $reader->load($xlsxFilename);

        return $spreadsheet->getSheetByName(self::SHEET_NAME)->toArray();
    }
}
