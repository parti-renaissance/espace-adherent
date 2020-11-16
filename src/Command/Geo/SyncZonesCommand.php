<?php

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
use App\Entity\Geo\Zone;
use App\Entity\Geo\ZoneableInterface;
use App\Repository\Geo\ZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SyncZonesCommand extends Command
{
    private const CLASSES = [
        Country::class,
        Region::class,
        Department::class,
        District::class,
        Canton::class,
        CityCommunity::class,
        City::class,
        Borough::class,
        CustomZone::class,
        ForeignDistrict::class,
        ConsularDistrict::class,
    ];

    protected static $defaultName = 'app:geo:sync-zones';

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
            ->setDescription('Create missing zone and link it to its parents')
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
        $this->io->title('Start syncing zones');

        $zonables = $this->findZonables();

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

        return 0;
    }

    /**
     * @return array|ZoneableInterface[]
     */
    private function findZonables(): iterable
    {
        $grouped = [];

        $this->io->section('Fetch zones from sources');

        foreach (self::CLASSES as $class) {
            $this->io->write(sprintf(' // "%s"', $class));

            $entities = $this->em
                ->createQueryBuilder()
                ->select(['x'])
                ->from($class, 'x')
                ->getQuery()
                ->getResult()
            ;

            $this->io->writeln(sprintf(' ... found %d', \count($entities)));

            $grouped[] = $entities;
        }

        $entities = array_merge(...$grouped);

        $this->io->comment(sprintf('Total %d', \count($entities)));

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
}
