<?php

namespace App\Command\ChezVous;

use App\ChezVous\Marker\DedoublementClasses;
use App\ChezVous\Marker\MaisonServiceAccueilPublic;
use App\ChezVous\Marker\MissionBern;
use App\ChezVous\MarkerChoiceLoader;
use App\ChezVous\Measure\DedoublementClasses as MeasureDedoublementClasses;
use App\ChezVous\Measure\MissionBern as MeasureMissionBern;
use App\ChezVous\MeasureChoiceLoader;
use App\Entity\ChezVous\City;
use App\Entity\ChezVous\Marker;
use App\Entity\ChezVous\Measure;
use App\Entity\ChezVous\MeasureType;
use App\Repository\ChezVous\CityRepository;
use App\Repository\ChezVous\MeasureRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportMarkersCommand extends AbstractImportCommand
{
    private const CSV_DIRECTORY = 'chez-vous/markers';

    protected static $defaultName = 'app:chez-vous:import-markers';

    private $markerChoiceLoader;
    private $measureChoiceLoader;
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
        FilesystemInterface $storage,
        MarkerChoiceLoader $markerChoiceLoader,
        MeasureChoiceLoader $measureChoiceLoader,
        MeasureRepository $measureRepository
    ) {
        parent::__construct($em, $cityRepository, $storage);

        $this->markerChoiceLoader = $markerChoiceLoader;
        $this->measureChoiceLoader = $measureChoiceLoader;
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

        return 0;
    }

    private function importMarkerType(string $type): void
    {
        if (!\in_array($type, $this->markerChoiceLoader->getTypeChoices(), true)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a known marker type. Known marker types are: "%s".', $type, implode('", "', $this->markerChoiceLoader->getTypeChoices())));
        }

        $this->io->section("Importing marker of type \"$type\"");

        $filename = sprintf('%s/%s.csv', self::CSV_DIRECTORY, $type);

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
                $this->em->clear(Marker::class);
                $this->em->clear(Measure::class);
                $this->em->clear(City::class);
            }
        }

        $this->em->clear(Marker::class);
        $this->em->clear(Measure::class);
        $this->em->clear(City::class);

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

                $measureType = $this->measureChoiceLoader->getMeasureType(DedoublementClasses::getType());

                if ($measure = $this->findMeasure($city, $measureType)) {
                    $measure->setPayload(MeasureDedoublementClasses::createPayload($totalCpCe1));

                    break;
                }

                $this->em->persist(MeasureDedoublementClasses::create($city, $measureType, $totalCpCe1));

                break;
            case MissionBern::class:
                $link = $metadata['link'];
                $montant = (int) preg_replace('/[^\d.]/', '', $metadata['montant']);

                $montant = $montant > 200 ? $montant : null;

                $measureType = $this->measureChoiceLoader->getMeasureType(MissionBern::getType());

                if ($measure = $this->findMeasure($city, $measureType)) {
                    $measure->setPayload(MeasureMissionBern::createPayload($link, $montant));

                    break;
                }

                $this->em->persist(MeasureMissionBern::create($city, $measureType, $link, $montant));

                break;
        }
    }

    private function findMeasure(City $city, MeasureType $type): ?Measure
    {
        return $this->measureRepository->findOneByCityAndType($city, $type);
    }
}
