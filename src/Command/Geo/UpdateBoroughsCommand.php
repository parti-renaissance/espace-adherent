<?php

declare(strict_types=1);

namespace App\Command\Geo;

use App\Command\Geo\Helper\Persister;
use App\Command\Geo\Helper\Summary;
use App\Entity\Geo\Borough;
use App\Entity\Geo\City;
use App\Entity\Geo\GeoInterface;
use App\Repository\Geo\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:geo:update-boroughs',
    description: 'Update french boroughs',
)]
final class UpdateBoroughsCommand extends Command
{
    private const BOROUGHS = [
        GeoInterface::CITY_PARIS_CODE => [
            ['code' => '75101', 'postalCode' => '75001', 'name' => 'Paris 1er'],
            ['code' => '75102', 'postalCode' => '75002', 'name' => 'Paris 2ème'],
            ['code' => '75103', 'postalCode' => '75003', 'name' => 'Paris 3ème'],
            ['code' => '75104', 'postalCode' => '75004', 'name' => 'Paris 4ème'],
            ['code' => '75105', 'postalCode' => '75005', 'name' => 'Paris 5ème'],
            ['code' => '75106', 'postalCode' => '75006', 'name' => 'Paris 6ème'],
            ['code' => '75107', 'postalCode' => '75007', 'name' => 'Paris 7ème'],
            ['code' => '75108', 'postalCode' => '75008', 'name' => 'Paris 8ème'],
            ['code' => '75109', 'postalCode' => '75009', 'name' => 'Paris 9ème'],
            ['code' => '75110', 'postalCode' => '75010', 'name' => 'Paris 10ème'],
            ['code' => '75111', 'postalCode' => '75011', 'name' => 'Paris 11ème'],
            ['code' => '75112', 'postalCode' => '75012', 'name' => 'Paris 12ème'],
            ['code' => '75113', 'postalCode' => '75013', 'name' => 'Paris 13ème'],
            ['code' => '75114', 'postalCode' => '75014', 'name' => 'Paris 14ème'],
            ['code' => '75115', 'postalCode' => '75015', 'name' => 'Paris 15ème'],
            ['code' => '75116', 'postalCode' => '75016', 'name' => 'Paris 16ème'],
            ['code' => '75117', 'postalCode' => '75017', 'name' => 'Paris 17ème'],
            ['code' => '75118', 'postalCode' => '75018', 'name' => 'Paris 18ème'],
            ['code' => '75119', 'postalCode' => '75019', 'name' => 'Paris 19ème'],
            ['code' => '75120', 'postalCode' => '75020', 'name' => 'Paris 20ème'],
        ],
        GeoInterface::CITY_MARSEILLE_CODE => [
            ['code' => '13201', 'postalCode' => '13001', 'name' => 'Marseille 1er'],
            ['code' => '13202', 'postalCode' => '13002', 'name' => 'Marseille 2ème'],
            ['code' => '13203', 'postalCode' => '13003', 'name' => 'Marseille 3ème'],
            ['code' => '13204', 'postalCode' => '13004', 'name' => 'Marseille 4ème'],
            ['code' => '13205', 'postalCode' => '13005', 'name' => 'Marseille 5ème'],
            ['code' => '13206', 'postalCode' => '13006', 'name' => 'Marseille 6ème'],
            ['code' => '13207', 'postalCode' => '13007', 'name' => 'Marseille 7ème'],
            ['code' => '13208', 'postalCode' => '13008', 'name' => 'Marseille 8ème'],
            ['code' => '13209', 'postalCode' => '13009', 'name' => 'Marseille 9ème'],
            ['code' => '13210', 'postalCode' => '13010', 'name' => 'Marseille 10ème'],
            ['code' => '13211', 'postalCode' => '13011', 'name' => 'Marseille 11ème'],
            ['code' => '13212', 'postalCode' => '13012', 'name' => 'Marseille 12ème'],
            ['code' => '13213', 'postalCode' => '13013', 'name' => 'Marseille 13ème'],
            ['code' => '13214', 'postalCode' => '13014', 'name' => 'Marseille 14ème'],
            ['code' => '13215', 'postalCode' => '13015', 'name' => 'Marseille 15ème'],
            ['code' => '13216', 'postalCode' => '13016', 'name' => 'Marseille 16ème'],
            ['code' => '13217', 'postalCode' => '13017', 'name' => 'Marseille 17ème'],
        ],
        GeoInterface::CITY_LYON_CODE => [
            ['code' => '69381', 'postalCode' => '69001', 'name' => 'Lyon 1er'],
            ['code' => '69382', 'postalCode' => '69002', 'name' => 'Lyon 2ème'],
            ['code' => '69383', 'postalCode' => '69003', 'name' => 'Lyon 3ème'],
            ['code' => '69384', 'postalCode' => '69004', 'name' => 'Lyon 4ème'],
            ['code' => '69385', 'postalCode' => '69005', 'name' => 'Lyon 5ème'],
            ['code' => '69386', 'postalCode' => '69006', 'name' => 'Lyon 6ème'],
            ['code' => '69387', 'postalCode' => '69007', 'name' => 'Lyon 7ème'],
            ['code' => '69388', 'postalCode' => '69008', 'name' => 'Lyon 8ème'],
            ['code' => '69389', 'postalCode' => '69009', 'name' => 'Lyon 9ème'],
        ],
    ];

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var CityRepository
     */
    private $cityRepository;

    /**
     * @var EntityRepository
     */
    private $boroughRepository;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var Collection
     */
    private $entities;

    public function __construct(EntityManagerInterface $em, CityRepository $cityRepository)
    {
        $this->em = $em;
        $this->cityRepository = $cityRepository;

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
        $this->boroughRepository = $this->em->getRepository(Borough::class);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Start updating french boroughs');

        //
        // Fetch all entities to memory
        // We'll update or mark some as inactive
        //

        $this->io->section('Fetching entities from database');

        $this->io->comment("All entities are marked as inactive, once it's found they become back active");

        $this->populateEntities();

        //
        // Processing boroughs
        //

        $this->io->section('Processing boroughs');

        $this->io->progressStart(array_sum(array_map('count', self::BOROUGHS)));

        foreach (self::BOROUGHS as $cityCode => $boroughsPerCity) {
            $city = $this->cityRepository->findOneBy(['code' => $cityCode]);
            if (!$city) {
                throw new \RuntimeException(\sprintf('City %s not found', $cityCode));
            }

            foreach ($boroughsPerCity as $entry) {
                $this->processEntry($entry, $city);
                $this->io->progressAdvance();
            }
        }

        $this->io->progressFinish();

        //
        // Summary and persisting
        //

        $summary = new Summary($this->io);
        $summary->run($this->entities);

        $persister = new Persister($this->io, $this->em);
        $persister->run($this->entities, $input->getOption('dry-run'));

        return self::SUCCESS;
    }

    private function populateEntities(): void
    {
        /* @var GeoInterface[] $entities */
        $entities = $this->em->getRepository(Borough::class)->findAll();
        foreach ($entities as $entity) {
            $key = Borough::class.'#'.$entity->getCode();
            $this->entities->set($key, $entity);

            // Mark it as inactive, if it's present in the source, it becomes back active
            $entity->activate(false);
        }
    }

    private function processEntry(array $entry, City $city): void
    {
        $key = Borough::class.'#'.$entry['code'];
        $borough = $this->entities->get($key);

        if (!$borough) {
            $borough = $this->boroughRepository->findOneBy(['code' => $entry['code']]);
            if (!$borough) {
                $borough = new Borough(
                    $entry['code'],
                    $entry['name'],
                    $city
                );
            }

            $this->entities->set($key, $borough);
        }

        $borough->activate();
        $borough->setName($entry['name']);
        $borough->setPostalCode([$entry['postalCode']]);
    }
}
