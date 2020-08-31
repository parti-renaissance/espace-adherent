<?php

namespace App\Command\Geo;

use App\Command\Geo\Helper\Persister;
use App\Command\Geo\Helper\Summary;
use App\Entity\Geo\City;
use App\Entity\Geo\CollectivityInterface;
use App\Entity\Geo\Country;
use App\Entity\Geo\Department;
use App\Entity\Geo\Region;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class UpdateFranceCommand extends Command
{
    private const FRANCE_CODE = 'FR';
    private const API_PATH = '/communes?fields=code,nom,codesPostaux,population,departement,region';

    protected static $defaultName = 'app:geo:update-france';

    /**
     * @var HttpClientInterface
     */
    private $apiClient;

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

    public function __construct(HttpClientInterface $geoGouvApiClient, EntityManagerInterface $em)
    {
        $this->apiClient = $geoGouvApiClient;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Update french administrative divisions according to geo.api.gouv.fr')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Execute the algorithm without persisting any data.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->entities = new ArrayCollection();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Start updating french administrative division');

        //
        // Fetch all entities to memory
        // We'll update or mark some as inactive
        //

        $this->io->section('Fetching entities from database');

        $this->io->comment("All entities are marked as inactive, once it's found they become back active");

        $this->populateEntities();

        //
        // Requesting API
        //

        $this->io->section('Requesting API');

        $entries = $this->apiClient->request('GET', self::API_PATH)->toArray();

        $this->io->comment([
            sprintf('%d cities found', \count($entries)),
            'Regions and departments will be extracted from these cities',
        ]);

        //
        // Processing entries
        //

        $this->io->section('Processing entries');

        $this->io->progressStart(\count($entries));
        foreach ($entries as $entry) {
            $this->processEntry($entry);
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();

        //
        // Summary and persisting
        //

        $summary = new Summary($this->io);
        $summary->run($this->entities);

        $persister = new Persister($this->io, $this->em);
        $persister->run($this->entities, $input->getOption('dry-run'));

        return 0;
    }

    private function processEntry(array $entry): void
    {
        /* @var Country|null $region */
        $france = $this->retrieveEntity(Country::class, self::FRANCE_CODE, static function () {
            return new Country(self::FRANCE_CODE, 'France');
        });

        /* @var Region|null $region */
        $region = null;
        if (isset($entry['region']['code'])) {
            $region = $this->retrieveEntity(
                Region::class,
                $entry['region']['code'],
                static function () use ($entry, $france): Region {
                    return new Region(
                        $entry['region']['code'],
                        $entry['region']['nom'],
                        $france
                    );
                }
            );

            $region->setName($entry['region']['nom']);
        }

        /* @var Department|null $department */
        $department = null;
        if ($region && isset($entry['departement']['code'])) {
            $department = $this->retrieveEntity(
                Department::class,
                $entry['departement']['code'],
                static function () use ($entry, $region): Department {
                    return new Department(
                        $entry['departement']['code'],
                        $entry['departement']['nom'],
                        $region
                    );
                }
            );

            $department->setName($entry['departement']['nom']);
            $department->setRegion($region);
        }

        /* @var City $city */
        $city = $this->retrieveEntity(
            City::class,
            $entry['code'],
            static function () use ($entry): City {
                return new City($entry['code'], $entry['nom']);
            }
        );

        $city->setName($entry['nom']);
        $city->setPostalCode($entry['codesPostaux'] ?? []);
        $city->setPopulation($entry['population'] ?? null);
        $city->setDepartment($department);
    }

    private function populateEntities(): void
    {
        /* @var CollectivityInterface[] $entities */
        $entities = array_merge(
            $this->em->getRepository(Region::class)->findAll(),
            $this->em->getRepository(Department::class)->findAll(),
            $this->em->getRepository(City::class)->findAll(),
        );

        foreach ($entities as $entity) {
            $key = \get_class($entity).'#'.$entity->getCode();
            $this->entities->set($key, $entity);

            // Mark it as inactive, if it's present in the API, it becomes back active
            $entity->activate(false);
        }
    }

    /**
     * @return CollectivityInterface
     *
     * @throws \RuntimeException When entity doesn't exist in database and $factory argument isn't given
     */
    private function retrieveEntity(string $class, string $code, callable $factory = null): object
    {
        $key = $class.'#'.$code;

        if (!$this->entities->containsKey($key)) {
            $repository = $this->em->getRepository($class);

            /* @var CollectivityInterface $entity */
            $entity = $repository->findOneBy(['code' => $code]);
            if (!$entity) {
                if (!$factory) {
                    throw new \RuntimeException(sprintf('Entity %s not found', $key));
                }

                $entity = $factory();
            }

            $this->entities->set($key, $entity);
        }

        $entity = $this->entities->get($key);

        // Activate entity, once it's found in the API
        $entity->activate();

        return $entity;
    }
}
