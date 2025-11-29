<?php

declare(strict_types=1);

namespace App\Command\Geo;

use App\Command\Geo\Helper\Persister;
use App\Command\Geo\Helper\Summary;
use App\Entity\Geo\Borough;
use App\Entity\Geo\Canton;
use App\Entity\Geo\City;
use App\Entity\Geo\CityCommunity;
use App\Entity\Geo\ConsularDistrict;
use App\Entity\Geo\Country;
use App\Entity\Geo\CustomZone;
use App\Entity\Geo\Department;
use App\Entity\Geo\District;
use App\Entity\Geo\ForeignDistrict;
use App\Entity\Geo\Region;
use App\Entity\Geo\VotePlace;
use App\Entity\Geo\Zone;
use App\Entity\Geo\ZoneableInterface;
use App\Repository\Geo\ZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:geo:sync-zones',
    description: 'Create missing zone and link it to its parents',
)]
final class SyncZonesCommand extends Command
{
    private const CLASSES = [
        Zone::COUNTRY => Country::class,
        Zone::REGION => Region::class,
        Zone::DEPARTMENT => Department::class,
        Zone::DISTRICT => District::class,
        Zone::CANTON => Canton::class,
        Zone::CITY_COMMUNITY => CityCommunity::class,
        Zone::CITY => City::class,
        Zone::BOROUGH => Borough::class,
        Zone::CUSTOM => CustomZone::class,
        Zone::FOREIGN_DISTRICT => ForeignDistrict::class,
        Zone::CONSULAR_DISTRICT => ConsularDistrict::class,
        Zone::VOTE_PLACE => VotePlace::class,
    ];

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
     * @var ZoneRepository
     */
    private $zoneRepository;

    public function __construct(EntityManagerInterface $em, ZoneRepository $zoneRepository)
    {
        $this->em = $em;
        $this->zoneRepository = $zoneRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Execute the algorithm but will not persist in database.')
            ->addOption('types', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Types to synchronize')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->entities = new ArrayCollection();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Start syncing zones');

        $types = $input->getOption('types');

        $zonables = $this->findZonables($types);

        $this->io->section('Transforming source entities into zones');

        $this->io->progressStart(\count($zonables));

        foreach ($zonables as $zonable) {
            $this->zoneableAsZone($zonable);
            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $summary = new Summary($this->io);
        $summary->run($this->entities);

        $persister = new Persister($this->io, $this->em);
        $persister->run($this->entities, $input->getOption('dry-run'));

        return self::SUCCESS;
    }

    /**
     * @return array|ZoneableInterface[]
     */
    private function findZonables(array $types): iterable
    {
        $grouped = [];

        $this->io->section('Fetch zones from sources');

        foreach ($this->getClasses($types) as $class) {
            $this->io->write(\sprintf(' // "%s"', $class));

            $entities = $this->em
                ->createQueryBuilder()
                ->select(['x'])
                ->from($class, 'x')
                ->getQuery()
                ->getResult()
            ;

            $this->io->writeln(\sprintf(' ... found %d', \count($entities)));

            $grouped[] = $entities;
        }

        $entities = array_merge(...$grouped);

        $this->io->comment(\sprintf('Total %d', \count($entities)));

        return $entities;
    }

    private function zoneableAsZone(ZoneableInterface $zoneable): Zone
    {
        $key = $zoneable->getZoneType().'#'.$zoneable->getCode();
        if ($this->entities->containsKey($key)) {
            return $this->entities->get($key);
        }

        $zone = $this->zoneRepository->zoneableAsZone($zoneable);
        $this->entities->set($key, $zone);

        $zone->clearParents();
        foreach ($zoneable->getParents() as $zoneableParent) {
            $zone->addParent($this->zoneableAsZone($zoneableParent));
        }

        return $zone;
    }

    private function getClasses(array $types): array
    {
        return $types ? array_intersect_key(self::CLASSES, array_flip($types)) : self::CLASSES;
    }
}
