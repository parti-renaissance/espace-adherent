<?php

namespace AppBundle\Command\ChezVous;

use AppBundle\ChezVous\Marker\DedoublementClasses;
use AppBundle\ChezVous\MarkerChoiceLoader;
use AppBundle\Repository\ChezVous\CityRepository;
use AppBundle\Repository\ChezVous\MarkerRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportMarkersCommand extends AbstractImportCommand
{
    private const CSV_DIRECTORY = 'chez-vous/markers';

    protected static $defaultName = 'app:chez-vous:import-markers';

    private $markerRepository;
    private $markerChoiceLoader;

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
        MarkerChoiceLoader $markerChoiceLoader
    ) {
        parent::__construct($em, $cityRepository, $storage);

        $this->markerRepository = $markerRepository;
        $this->markerChoiceLoader = $markerChoiceLoader;
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
            default:
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
    }
}
