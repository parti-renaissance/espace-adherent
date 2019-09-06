<?php

namespace AppBundle\Command\ChezVous;

use AppBundle\Entity\ChezVous\City;
use AppBundle\Entity\ChezVous\Department;
use AppBundle\Entity\ChezVous\Region;
use AppBundle\Repository\ChezVous\CityRepository;
use AppBundle\Repository\ChezVous\DepartmentRepository;
use AppBundle\Repository\ChezVous\RegionRepository;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportZipCodesCommand extends AbstractImportCommand
{
    private const CSV_DIRECTORY = 'chez-vous/zipcodes';
    private const CSV_REGIONS = 'regions.csv';
    private const CSV_DEPARTMENTS = 'departments.csv';
    private const CSV_CITIES = 'cities.csv';

    protected static $defaultName = 'app:chez-vous:import-zipcodes';

    private $slugify;
    private $regionRepository;
    private $departmentRepository;

    public function __construct(
        EntityManagerInterface $em,
        CityRepository $cityRepository,
        Filesystem $storage,
        SlugifyInterface $slugify,
        RegionRepository $regionRepository,
        DepartmentRepository $departmentRepository
    ) {
        parent::__construct($em, $cityRepository, $storage);

        $this->slugify = $slugify;
        $this->regionRepository = $regionRepository;
        $this->departmentRepository = $departmentRepository;
    }

    protected function configure()
    {
        $this->setDescription('Import ChezVous zipcodes from CSV files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('ChezVous zipcodes import.');

        $this->em->beginTransaction();

        $this->importRegions();
        $this->importDepartments();
        $this->importCities();

        $this->em->commit();

        $this->io->success('ChezVous zipcodes imported successfully!');
    }

    private function importRegions(): void
    {
        $this->io->section('Starting regions import.');

        $reader = $this->createReader(self::CSV_DIRECTORY.'/'.self::CSV_REGIONS);

        $this->io->progressStart($total = $reader->count());

        $line = 2;
        foreach ($reader as $index => $row) {
            $code = $row['code'];
            $name = $row['name'];

            if (empty($code)) {
                throw new \RuntimeException("No code found for region. (line $line)");
            }

            if (empty($name)) {
                throw new \RuntimeException("No name found for region \"$code\". (line $line)");
            }

            $this->em->persist(new Region($name, $code));

            $this->io->progressAdvance();
            ++$line;
        }

        $this->em->flush();
        $this->em->clear();

        $this->io->progressFinish();

        $this->io->comment("Processed $total regions.");
    }

    private function importDepartments(): void
    {
        $this->io->section('Starting departments import');

        $reader = $this->createReader(self::CSV_DIRECTORY.'/'.self::CSV_DEPARTMENTS);

        $this->io->progressStart($total = $reader->count());

        $line = 2;
        foreach ($reader as $index => $row) {
            $regionCode = $row['region_code'];
            $code = $row['code'];
            $name = $row['name'];
            $label = $row['label'];

            if (empty($regionCode)) {
                throw new \RuntimeException("No region_code found for department. (line $line)");
            }

            if (!$region = $this->findRegion($regionCode)) {
                throw new \RuntimeException("No region with code \"$regionCode\" found for department. (line $line)");
            }

            if (empty($code)) {
                throw new \RuntimeException("No code found for department. (line $line)");
            }

            if (empty($name)) {
                throw new \RuntimeException("No name found for department \"$code\". (line $line)");
            }

            if (empty($label)) {
                $label = $name;
            }

            $this->em->persist(new Department($region, $name, $label, $code));

            $this->io->progressAdvance();
            ++$line;
        }

        $this->em->flush();
        $this->em->clear();

        $this->io->progressFinish();

        $this->io->comment("Processed $total departments.");
    }

    private function importCities(): void
    {
        $this->io->section('Cities import.');

        $reader = $this->createReader(self::CSV_DIRECTORY.'/'.self::CSV_CITIES);

        $this->io->progressStart($total = $reader->count());

        $line = 2;
        foreach ($reader as $index => $row) {
            $departmentCode = $row['department_code'];
            $inseeCode = $row['insee_code'];
            $postalCode = $row['zip_code'];
            $name = $row['name'];
            $latitude = $row['gps_lat'];
            $longitude = $row['gps_lng'];

            if (empty($name)) {
                $this->io->text("No name found for city (line $line). Skipping.");

                continue;
            }

            if (empty($postalCode)) {
                $this->io->text("No postal_code found for city \"$name\" (line $line). Skipping.");

                continue;
            }

            if (empty($inseeCode)) {
                $this->io->text("No insee_code found for city \"$name\" (line $line). Skipping.");

                continue;
            }

            if (empty($departmentCode)) {
                $this->io->text("No department_code found for city \"$name\" (line $line). Skipping.");

                continue;
            }

            if (!$department = $this->findDepartment($departmentCode)) {
                $this->io->text("No department with code \"$departmentCode\" found for city \"$name\" (line $line). Skipping.");

                continue;
            }

            $inseeCode = City::normalizeCode($inseeCode);

            if ($city = $this->findCity($inseeCode)) {
                $city->addPostalCode($postalCode);
            } else {
                $slug = $this->slugify->slugify("$inseeCode-$name");

                $this->em->persist(new City($department, $name, [$postalCode], $inseeCode, $slug, $latitude, $longitude));
            }

            $this->em->flush();

            $this->io->progressAdvance();
            ++$line;

            if (0 === ($line % self::BATCH_SIZE)) {
                $this->em->clear();
            }
        }

        $this->em->clear();

        $this->io->progressFinish();

        $this->io->comment("Processed $total cities.");
    }

    private function findRegion(string $code): ?Region
    {
        return $this->regionRepository->findOneByCode($code);
    }

    private function findDepartment(string $code): ?Department
    {
        return $this->departmentRepository->findOneByCode($code);
    }
}
