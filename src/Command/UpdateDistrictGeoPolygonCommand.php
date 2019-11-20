<?php

namespace AppBundle\Command;

use AppBundle\Entity\District;
use AppBundle\Entity\GeoData;
use AppBundle\Geo\GeometryFactory;
use AppBundle\Repository\DistrictRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateDistrictGeoPolygonCommand extends Command
{
    protected static $defaultName = 'app:district:update-polygon';

    private $em;
    /** @var SymfonyStyle */
    private $io;
    private $storage;
    private $districtRepository;

    private $districtsGeoJson;
    private $countriesGeoJson;

    public function __construct(
        EntityManagerInterface $em,
        FilesystemInterface $storage,
        DistrictRepository $repository
    ) {
        $this->em = $em;
        $this->storage = $storage;
        $this->districtRepository = $repository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Update GeoJson polygons of all districts')
            ->addOption('districts-file', null, InputOption::VALUE_REQUIRED, 'GeoJSON file of french districts to load')
            ->addOption('countries-file', null, InputOption::VALUE_REQUIRED, 'GeoJSON file of countries to load')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($districtsFile = $input->getOption('districts-file')) {
            $this->districtsGeoJson = json_decode($this->storage->read($districtsFile), true);
        }

        if ($countriesFile = $input->getOption('countries-file')) {
            $this->countriesGeoJson = json_decode($this->storage->read($countriesFile), true);
        }

        if (empty($this->districtsGeoJson) && empty($this->countriesGeoJson)) {
            throw new \InvalidArgumentException('At least one file should be passed');
        }

        $districts = $this->districtRepository->findAll();

        $this->io->progressStart(\count($districts));

        /** @var District $district */
        foreach ($districts as $district) {
            if ($geoData = $this->getNewGeoData($district)) {
                $oldGeoData = $district->getGeoData();

                $district->setGeoData($geoData);
                $this->em->flush();

                $this->em->remove($oldGeoData);
                $this->em->flush();

                $this->io->progressAdvance();
            }
        }
    }

    private function getNewGeoData(District $district): ?GeoData
    {
        $dptCode = $district->getDepartmentCode();

        if ('999' === $dptCode) {
            $polygons = $this->getCountriesPolygons($district->getCountries());
        } else {
            $polygons = $this->getDistrictPolygon(
                str_pad($dptCode, 3, '0', \STR_PAD_LEFT),
                str_pad($district->getNumber(), 2, '0', \STR_PAD_LEFT)
            );
        }

        if (empty($polygons)) {
            return null;
        }

        return new GeoData(GeometryFactory::createGeometry($polygons));
    }

    private function getCountriesPolygons(array $countryCodes): array
    {
        if (empty($this->countriesGeoJson)) {
            return [];
        }

        return $this->findPolygons($this->countriesGeoJson, 'ISO_A2', $countryCodes);
    }

    private function getDistrictPolygon(string $dptCode, string $districtCode): array
    {
        if (empty($this->districtsGeoJson)) {
            return [];
        }

        return $this->findPolygons($this->districtsGeoJson, 'REF', ["${dptCode}-${districtCode}"]);
    }

    private function findPolygons(array $data, string $key, array $identifiers): array
    {
        $features = [];

        foreach ($data['features'] as $feature) {
            if (\in_array($feature['properties'][$key], $identifiers, true)) {
                $features[] = $feature;
            }
        }

        return $features;
    }
}
