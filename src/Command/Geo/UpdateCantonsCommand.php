<?php

declare(strict_types=1);

namespace App\Command\Geo;

use App\Command\Geo\Helper\Persister;
use App\Command\Geo\Helper\Summary;
use App\Entity\Geo\Canton;
use App\Entity\Geo\City;
use App\Entity\Geo\Department;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:geo:update-cantons',
    description: 'Update french cantons according to INSEE data',
)]
final class UpdateCantonsCommand extends Command
{
    private const CANTONS_SOURCE = 'https://www.insee.fr/fr/statistiques/fichier/4316069/canton2020-csv.zip';
    private const CANTONS_FILENAME = 'canton2020.csv';

    private const CITIES_SOURCE = 'https://www.insee.fr/fr/statistiques/fichier/4316069/communes2020-csv.zip';
    private const CITIES_FILENAME = 'communes2020.csv';

    private const CANTON_TYPES = ['C', 'V'];
    private const CITY_TYPES = ['COM'];

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
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Start updating french cantons');

        $this->populateCantonsFromDatabase();
        $this->populateCitiesFromDatabase();
        $this->loadCantonsFromSource();
        $this->loadExtraLinksToCitiesFromSource();

        $summary = new Summary($this->io);
        $summary->run($this->entities);

        $persister = new Persister($this->io, $this->em);
        $persister->run($this->entities, $input->getOption('dry-run'));

        return self::SUCCESS;
    }

    private function populateCantonsFromDatabase(): void
    {
        $this->io->section('Fetching cantons from database');
        $this->io->comment([
            'It marks entities as inactive, they become back active if they are found in the source',
            "It erases links to cities because we'll rebuild them in the following steps",
        ]);

        $cantons = $this->em->getRepository(Canton::class)->findAll();
        foreach ($cantons as $canton) {
            $key = Canton::class.'#'.$canton->getCode();
            $this->entities->set($key, $canton);
            $canton->clearCities();
            $canton->activate(false);
        }
    }

    private function populateCitiesFromDatabase(): void
    {
        $this->io->section('Fetching cities from database');
        $this->io->comment("It erases links to cantons because we'll rebuild them in the following steps");

        $cities = $this->em->getRepository(City::class)->findAll();
        foreach ($cities as $city) {
            $key = City::class.'#'.$city->getCode();
            $this->entities->set($key, $city);
            $city->clearCantons();
        }
    }

    private function loadCantonsFromSource(): void
    {
        $this->io->section('Loading cantons from source');
        $this->io->comment(\sprintf('Fetching data from %s', self::CANTONS_SOURCE));

        $file = $this->openUrlAsFile(self::CANTONS_SOURCE, self::CANTONS_FILENAME);
        $header = fgetcsv($file);
        while (false !== ($data = fgetcsv($file, 1000, ','))) {
            $row = array_combine($header, $data);

            // Skips non-canton entry
            if (!\in_array($row['typect'], self::CANTON_TYPES, true)) {
                continue;
            }

            $department = $this->em->getRepository(Department::class)->findOneBy(['code' => $row['dep']]);
            if (!$department) {
                throw new \RuntimeException(\sprintf('Department %s not found for canton %s (%s)', $row['dep'], $row['libelle'], $row['can']));
            }

            $key = Canton::class.'#'.$row['can'];
            $canton = $this->entities->get($key);
            if (!$canton) {
                $canton = new Canton($row['can'], $row['libelle'], $department);
                $this->entities->set($key, $canton);
            }

            // Updates general data
            $canton->setName($row['libelle']);
            $canton->setDepartment($department);

            // Activates entity, once it's found in the source
            $canton->activate();

            // Links canton to its main city
            $city = $row['burcentral'] ? $this->retrieveCity($row['burcentral']) : null;
            if ($city) {
                $canton->addCity($city);
            }
        }

        fclose($file);
    }

    private function loadExtraLinksToCitiesFromSource(): void
    {
        $this->io->section('Linking cantons to cities');
        $this->io->comment(\sprintf('Fetching data from %s', self::CANTONS_SOURCE));

        $file = $this->openUrlAsFile(self::CITIES_SOURCE, self::CITIES_FILENAME);
        $header = fgetcsv($file);
        while (false !== ($data = fgetcsv($file, 1000, ','))) {
            $row = array_combine($header, $data);

            // Skips non-city entry
            if (!\in_array($row['typecom'], self::CITY_TYPES, true)) {
                continue;
            }

            // Skips non-existing city
            // That's not the place to create it
            $city = $this->retrieveCity($row['com']);
            if (!$city) {
                continue;
            }

            // Skips non-existing canton
            // That's not the place to create it
            /* @var Canton $canton */
            $canton = $this->entities->get(Canton::class.'#'.$row['can']);
            if (!$canton) {
                continue;
            }

            // Links them
            $canton->addCity($city);
        }

        fclose($file);
    }

    private function retrieveCity(string $code): ?City
    {
        $key = City::class.'#'.$code;
        if (!$this->entities->containsKey($key)) {
            $this->io->caution(\sprintf('City %s not found in database', $code));
        }

        return $this->entities->get($key);
    }

    /**
     * @return resource
     */
    private function openUrlAsFile(string $zipUrl, string $filename)
    {
        $response = $this->httpClient->request('GET', $zipUrl);

        $dir = \sprintf('%s/%s', sys_get_temp_dir(), uniqid(md5($zipUrl), true));

        $zipFilename = $dir.'.zip';
        $csvFilename = $dir.'/'.$filename;

        file_put_contents($zipFilename, $response->getContent());

        $zip = new \ZipArchive();

        if (true !== ($code = $zip->open($zipFilename))) {
            throw new \RuntimeException(\sprintf('ZipArchive::open() error: %d', $code));
        }

        if (!$zip->extractTo($dir)) {
            throw new \RuntimeException('Error during extracting data from archive');
        }

        $zip->close();

        return fopen($csvFilename, 'rb');
    }
}
