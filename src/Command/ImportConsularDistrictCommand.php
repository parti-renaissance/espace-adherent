<?php

namespace App\Command;

use App\Entity\ConsularDistrict;
use App\Geocoder\GeocoderInterface;
use App\Repository\ConsularDistrictRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Intl\Countries;

class ImportConsularDistrictCommand extends Command
{
    protected static $defaultName = 'app:consular-district:import';

    /**
     * @var SymfonyStyle
     */
    private $io;
    private $manager;
    private $storage;
    private $repository;
    private $countries;
    private $geocoder;

    public function __construct(
        FilesystemInterface $storage,
        EntityManagerInterface $em,
        ConsularDistrictRepository $repository,
        GeocoderInterface $geocoder
    ) {
        $this->storage = $storage;
        $this->manager = $em;
        $this->repository = $repository;
        $this->geocoder = $geocoder;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Import')
            ->addArgument('file', InputArgument::REQUIRED, 'File CSV to import')
            ->addOption('with-points', null, InputOption::VALUE_NONE, 'Create a geo point for each city')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->countries = Countries::getNames('FR_fr');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->storage->has($file = $input->getArgument('file'))) {
            throw new \InvalidArgumentException('File does not exist');
        }

        $csv = Reader::createFromStream($this->storage->readStream($file));
        $csv->setHeaderOffset(0);
        $csv->setDelimiter(';');

        $this->io->progressStart($csv->count());

        $withPoint = $input->getOption('with-points');

        foreach ($csv as $row) {
            $this->saveDistrict($this->buildDistrict($row, $withPoint));
            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
    }

    private function buildDistrict(array $row, bool $withPoint = false): ConsularDistrict
    {
        $codes = array_values(array_filter(array_map(function (string $country) {
            $code = array_search($country, $this->countries, true);

            if (!$code) {
                throw new \RuntimeException(sprintf('Country "%s" does not match any code', $country));
            }

            return $code;
        }, array_map('trim', explode(',', $row['Circonscription'])))));

        $cities = array_map('trim', explode(',', $row['Consulat']));

        $district = new ConsularDistrict(
            $codes,
            $cities,
            implode('_', array_merge($codes, [str_pad($row['N. circo'], 2, '0', \STR_PAD_LEFT)])),
            $row['N. circo']
        );

        if ($withPoint) {
            $this->bindPoints($district);
        }

        return $district;
    }

    private function saveDistrict(ConsularDistrict $district): void
    {
        if ($districtFromDb = $this->repository->findByCode($district->getCode())) {
            $districtFromDb->update($district);
        } else {
            $this->manager->persist($district);
        }

        $this->manager->flush();
    }

    private function bindPoints(ConsularDistrict $district): void
    {
        $district->clearPoints();

        foreach ($district->getCities() as $city) {
            $coordinates = $this->geocoder->geocode($city);

            $district->addPoint($coordinates->getLatitude(), $coordinates->getLongitude(), $city);
        }
    }
}
