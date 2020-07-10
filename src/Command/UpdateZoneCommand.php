<?php

namespace App\Command;

use App\Entity\ElectedRepresentative\Zone;
use App\Entity\ElectedRepresentative\ZoneCategory;
use App\Entity\Geo\Canton;
use App\Entity\Geo\City;
use App\Entity\Geo\CityCommunity;
use App\Entity\Geo\Country;
use App\Entity\Geo\Department;
use App\Entity\Geo\District;
use App\Entity\Geo\GeoInterface;
use App\Entity\Geo\Region;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class UpdateZoneCommand extends Command
{
    private const CATEGORIES = [
        Region::class => ZoneCategory::REGION,
        Department::class => ZoneCategory::DEPARTMENT,
        District::class => ZoneCategory::DISTRICT,
        City::class => ZoneCategory::CITY,
    ];

    protected static $defaultName = 'app:zone:update';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var ArrayCollection
     */
    private $zones;

    /**
     * @var ArrayCollection
     */
    private $geo;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->zones = new ArrayCollection();
        $this->geo = new ArrayCollection();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Update zone accordingly to geographic data')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Execute the algorithm without persisting any data.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Start updating zones');

        //
        // Transform geo units into zones
        //

//        $this->io->section('Reading countries from geo database');
//        $this->loadZonesByGeo(Country::class);

        $this->io->section('Reading regions from geo database');
        $this->loadZonesByGeo(Region::class);

        $this->io->section('Reading departments from geo database');
        $this->loadZonesByGeo(Department::class);

        $this->io->section('Reading cantons from geo database');
        $this->loadZonesByGeo(Canton::class);

        $this->io->section('Reading city communities from geo database');
        $this->loadZonesByGeo(CityCommunity::class);

        $this->io->section('Reading districts from geo database');
        $this->loadZonesByGeo(District::class);

        $this->io->section('Reading cities from geo database');
        $this->loadZonesByGeo(City::class);

        //
        // Update relation among zones
        //

        $this->io->section('Update relations');

        $this->io->progressStart($this->zones->count());
        foreach ($this->zones as $zone) {
            $this->updateParents($zone);
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();

        //
        // Summary
        //

        $this->summary();

        //
        // Writing
        //

        $this->io->section('Persisting in database');

        $dryRun = $input->getOption('dry-run');
        if ($dryRun) {
            $this->io->comment('Nothing was persisted in database');

            return 0;
        }

        $this->em->transactional(function (EntityManagerInterface $em) {
            foreach ($this->zones as $entity) {
//                $em->persist($entity);
            }
        });

        $this->io->success('Done');

        dump($this->zones->count());
        dump(sprintf('%.02f MB', memory_get_peak_usage() / 1024 / 1024));

        return 0;
    }

    private function loadZonesByGeo(string $geoClass): void
    {
        /* @var EntityRepository $repository */
        $repository = $this->em->getRepository($geoClass);

        $this->io->progressStart($repository->count([]));

        $geoUnits = $repository->createQueryBuilder('x')->getQuery()->iterate();
        foreach ($geoUnits as $key => $geo) {
            /* @var GeoInterface $geo */
            $geo = current($geo);

            $zone = $this->geoToZone($geo);
            $zone->setName($geo->getName());
            $zone->setCode($geo->getCode());
            $zone->getParents()->clear();

            $this->geo->set(spl_object_hash($zone), $geo);

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
    }

    private function geoToZone(GeoInterface $geo): Zone
    {
        // Tries from memory

        $zoneKey = spl_object_hash($geo);
        if ($this->zones->containsKey($zoneKey)) {
            return $this->zones->get($zoneKey);
        }

        // Tries from database

        $categoryName = null;
        foreach (self::CATEGORIES as $class => $name) {
            if ($geo instanceof $class) {
                $categoryName = $name;
                break;
            }
        }

        $category = $this->em->getRepository(ZoneCategory::class)->findOneBy(['name' => $categoryName]);
        if (!$category) {
            throw new \RuntimeException(sprintf("Can't find category for %s entity", \get_class($geo)));
        }

        $repository = $this->em->getRepository(Zone::class);

        $zone = $repository->createQueryBuilder('z')
            ->andWhere('z.code = :code')
            ->setParameter('code', $geo->getCode())
            ->andWhere('z.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        // Creates a new instance

        if (!$zone) {
            $zone = new Zone();
            $zone->setCategory($category);
        }

        // Store relation in memory

        $this->zones->set($zoneKey, $zone);

        $geoKey = spl_object_hash($zone);
        $this->geo->set($geoKey, $geo);

        return $zone;
    }

    private function zoneToGeo(Zone $zone): GeoInterface
    {
        $key = spl_object_hash($zone);
        if ($this->geo->containsKey($key)) {
            return $this->geo->get($key);
        }

        throw new \RuntimeException(sprintf("Can't find geo entity for zone: %s", $zone->getCode()));
    }

    private function updateParents(Zone $zone): void
    {
        $zone->getParents()->clear();
        $geoParents = $this->getGeoParentChain($this->zoneToGeo($zone));

        foreach ($geoParents as $geo) {
            $zone->getParents()->add($this->geoToZone($geo));
        }
    }

    private function getGeoParentChain(GeoInterface $geo): array
    {
        if ($geo instanceof City) {
            return array_filter(array_merge(
                [$geo],
                $geo->getDepartment() ? $this->getGeoParentChain($geo->getDepartment()) : [],
                $geo->getCanton() ? $this->getGeoParentChain($geo->getCanton()) : [],
                $geo->getCityCommunity() ? $this->getGeoParentChain($geo->getCityCommunity()) : [],
                $geo->getDistrict() ? $this->getGeoParentChain($geo->getDistrict()) : [],
            ));
        }

        $chain = [$geo];

        if (
            $geo instanceof Canton ||
            $geo instanceof CityCommunity ||
            $geo instanceof District
        ) {
            $chain[] = $geo = $geo->getDepartment();
        }

        if ($geo instanceof Department) {
            $chain[] = $geo = $geo->getRegion();
        }

//        if ($geo instanceof Region) {
//            $chain[] = $geo = $geo->getCountry();
//        }

        return array_filter($chain);
    }

    private function summary(): void
    {
        $this->io->section('Summary');

        /* @var Collection|Zone[] $newRegions */
        $newZones = $this->zones->filter(static function ($entity) {
            return !$entity->getId();
        });

        $this->io->note(sprintf('%d new zones', $newZones->count()));

        if ($this->io->isVerbose()) {
            foreach ($newZones as $zone) {
                $this->io->text(sprintf('%s (%s)', $zone->getName(), $zone->getCode()));
            }
        }
    }
}
