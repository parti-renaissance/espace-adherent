<?php

namespace App\Command;

use App\Entity\District;
use App\Entity\GeoData;
use App\Geo\GeometryFactory;
use App\Repository\DistrictRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:district:update-polygon',
    description: 'Update GeoJson polygons of all districts',
)]
class UpdateDistrictGeoPolygonCommand extends Command
{
    private $em;
    /** @var SymfonyStyle */
    private $io;
    private $storage;
    private $districtRepository;

    private $districtsGeoJson;
    private $countriesGeoJson;

    public function __construct(
        EntityManagerInterface $em,
        FilesystemOperator $defaultStorage,
        DistrictRepository $repository
    ) {
        $this->em = $em;
        $this->storage = $defaultStorage;
        $this->districtRepository = $repository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('districts-file', null, InputOption::VALUE_REQUIRED, 'GeoJSON file of french districts to load')
            ->addOption('countries-file', null, InputOption::VALUE_REQUIRED, 'GeoJSON file of countries to load')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
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

        return self::SUCCESS;
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

        return $this->findPolygons($this->districtsGeoJson, 'REF', ["{$dptCode}-{$districtCode}"]);
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
