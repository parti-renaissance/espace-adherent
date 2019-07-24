<?php

namespace AppBundle\Command\ChezVous;

use AppBundle\ChezVous\Marker\DedoublementClasses;
use AppBundle\ChezVous\Marker\MaisonServiceAccueilPublic;
use AppBundle\ChezVous\Marker\MissionBern;
use AppBundle\ChezVous\MarkerChoiceLoader;
use AppBundle\ChezVous\Measure\DedoublementClasses as MeasureDedoublementClasses;
use AppBundle\ChezVous\Measure\MissionBern as MeasureMissionBern;
use AppBundle\Entity\ChezVous\City;
use AppBundle\Entity\ChezVous\Measure;
use AppBundle\Repository\ChezVous\CityRepository;
use AppBundle\Repository\ChezVous\MarkerRepository;
use AppBundle\Repository\ChezVous\MeasureRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportMarkersCommand extends AbstractImportCommand
{
    private const CSV_DIRECTORY = 'markers';

    protected static $defaultName = 'app:chez-vous:import-markers';

    private $markerRepository;
    private $markerChoiceLoader;
    private $measureRepository;

    protected function configure()
    {
        $this
            ->addArgument('type', InputArgument::OPTIONAL)
            ->setDescription('Import ChezVous markers from CSV files')
        ;
    }

    public function __construct(
        EntityManagerInterface $em,
        CityRepository $cityRepository,
        Filesystem $storage,
        MarkerRepository $markerRepository,
        MarkerChoiceLoader $markerChoiceLoader,
        MeasureRepository $measureRepository
    ) {
        parent::__construct($em, $cityRepository, $storage);

        $this->markerRepository = $markerRepository;
        $this->markerChoiceLoader = $markerChoiceLoader;
        $this->measureRepository = $measureRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');

        $this->io->title('ChezVous markers import');

        $this->em->beginTransaction();

        if ($type) {
            $this->importMarkerType($type);
        } else {
            foreach ($this->markerChoiceLoader->getTypeChoices() as $type) {
                $this->importMarkerType($type);
            }
        }

        $this->em->commit();

        $this->io->success('ChezVous markers imported successfully!');
    }

    private function importMarkerType(string $type): void
    {
        if (!\in_array($type, $this->markerChoiceLoader->getTypeChoices(), true)) {
            throw new \InvalidArgumentException(sprintf(
                '"%s" is not a known marker type. Known marker types are: "%s".',
                $type,
                implode('", "', $this->markerChoiceLoader->getTypeChoices())
            ));
        }

        $this->io->section("Importing marker of type \"$type\"");

        $filename = sprintf('%s/%s/%s.csv', self::ROOT_DIRECTORY, self::CSV_DIRECTORY, $type);

        if (!$this->storage->has($filename)) {
            $this->io->comment("No CSV found ($filename).");

            return;
        }

        $this->io->comment("Processing \"$filename\".");

        $reader = $this->createReader($filename);

        $this->io->progressStart($total = $reader->count());

        $count = 0;
        foreach ($reader as $row) {
            $this->loadMarker($type, $row);

            $this->em->flush();

            $this->io->progressAdvance();
            ++$count;

            if (0 === ($count % self::BATCH_SIZE)) {
                $this->em->clear();
            }
        }

        $this->em->clear();

        $this->io->progressFinish();

        $this->io->comment("Processed $total markers of type \"$type\".");
    }

    private function loadMarker(string $type, array $metadata): void
    {
        switch ($type) {
            case DedoublementClasses::getType():
                $this->loadGeocodedMarker(DedoublementClasses::class, $metadata);

                break;
            case MissionBern::getType():
                $this->loadGeocodedMarker(MissionBern::class, $metadata);

                break;
            case MaisonServiceAccueilPublic::getType():
                $this->loadGeocodedMarker(MaisonServiceAccueilPublic::class, $metadata);

                break;
        }
    }

    private function loadGeocodedMarker(string $markerClass, array $metadata): void
    {
        $inseeCode = $metadata['insee_code'];
        $latitude = $metadata['lat'];
        $longitude = $metadata['long'];

        if (empty($inseeCode)) {
            return;
        }

        $city = $this->findCity($inseeCode);

        if (!$city) {
            $this->io->text("No city found for insee_code \"$inseeCode\". Skipping.");

            return;
        }

        $this->em->persist($markerClass::createMarker($city, $latitude, $longitude));

        switch ($markerClass) {
            case DedoublementClasses::class:
                $totalCpCe1 = $metadata['total_cp_ce1'];

                if ($measure = $this->findMeasure($city, MeasureDedoublementClasses::getType())) {
                    $measure->setPayload(MeasureDedoublementClasses::createPayload($totalCpCe1));

                    break;
                }

                $this->em->persist(MeasureDedoublementClasses::create($city, $totalCpCe1));

                break;
            case MissionBern::class:
                $link = $metadata['link'];
                $montant = (int) preg_replace('/[^\d.]/', '', $metadata['montant']);

                $montant = $montant > 200 ? $montant : null;

                if ($measure = $this->findMeasure($city, MeasureMissionBern::getType())) {
                    $measure->setPayload(MeasureMissionBern::createPayload($link, $montant));

                    break;
                }

                $this->em->persist(MeasureMissionBern::create($city, $link, $montant));

                break;
        }
    }

    private function findMeasure(City $city, string $type): ?Measure
    {
        return $this->measureRepository->findOneByCityAndType($city, $type);
    }
}
