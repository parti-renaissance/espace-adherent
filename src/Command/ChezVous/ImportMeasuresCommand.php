<?php

namespace AppBundle\Command\ChezVous;

use AppBundle\ChezVous\Measure\BaisseNombreChomeurs;
use AppBundle\ChezVous\Measure\ChequeEnergie;
use AppBundle\ChezVous\Measure\ConversionSurfaceAgricoleBio;
use AppBundle\ChezVous\Measure\CouvertureFibre;
use AppBundle\ChezVous\Measure\CreationEntreprise;
use AppBundle\ChezVous\Measure\EmploisFrancs;
use AppBundle\ChezVous\Measure\MaisonServiceAccueilPublic;
use AppBundle\ChezVous\Measure\PassCulture;
use AppBundle\ChezVous\Measure\PrimeConversionAutomobile;
use AppBundle\ChezVous\Measure\QuartierReconqueteRepublicaine;
use AppBundle\ChezVous\Measure\SuppressionTaxeHabitation;
use AppBundle\ChezVous\MeasureChoiceLoader;
use AppBundle\Entity\ChezVous\City;
use AppBundle\Entity\ChezVous\Measure;
use AppBundle\Entity\ChezVous\MeasureType;
use AppBundle\Repository\ChezVous\CityRepository;
use AppBundle\Repository\ChezVous\MeasureRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportMeasuresCommand extends AbstractImportCommand
{
    private const CSV_DIRECTORY = 'chez-vous/measures';

    protected static $defaultName = 'app:chez-vous:import-measures';

    private $measureRepository;
    private $measureFactory;

    protected function configure()
    {
        $this
            ->addArgument('type', InputArgument::OPTIONAL)
            ->setDescription('Import ChezVous measures from CSV files')
        ;
    }

    public function __construct(
        EntityManagerInterface $em,
        CityRepository $cityRepository,
        Filesystem $storage,
        MeasureRepository $measureRepository,
        MeasureChoiceLoader $measureFactory
    ) {
        parent::__construct($em, $cityRepository, $storage);

        $this->measureRepository = $measureRepository;
        $this->measureFactory = $measureFactory;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');

        $this->io->title('ChezVous measures import');

        $this->em->beginTransaction();

        if ($type) {
            $this->importMeasureType($type);
        } else {
            foreach ($this->measureFactory->getTypeChoices() as $type) {
                $this->importMeasureType($type);
            }
        }

        $this->em->commit();

        $this->io->success('ChezVous measures imported successfully!');
    }

    private function importMeasureType(string $type): void
    {
        if (!\in_array($type, $this->measureFactory->getTypeChoices(), true)) {
            throw new \InvalidArgumentException(sprintf(
                '"%s" is not a known measure type. Known measure types are: "%s".',
                $type,
                implode('", "', $this->measureFactory->getTypeChoices())
            ));
        }

        $measureType = $this->measureFactory->getMeasureType($type);

        $this->io->section("Importing measures of type \"$type\"");

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
            $this->loadMeasure($measureType, $row);

            $this->em->flush();

            $this->io->progressAdvance();
            ++$count;

            if (0 === ($count % self::BATCH_SIZE)) {
                $this->em->clear(Measure::class);
                $this->em->clear(City::class);
            }
        }

        $this->em->clear(Measure::class);
        $this->em->clear(City::class);

        $this->io->progressFinish();

        $this->io->comment("Processed $total measures of type \"$type\".");
    }

    private function loadMeasure(MeasureType $measureType, array $metadata): void
    {
        switch ($measureType->getCode()) {
            case BaisseNombreChomeurs::getType():
                $this->loadMeasureBaisseNombreChomeurs($measureType, $metadata);

                break;
            case ChequeEnergie::getType():
                $this->loadMeasureChequeEnergie($measureType, $metadata);

                break;
            case ConversionSurfaceAgricoleBio::getType():
                $this->loadMeasureConversionSurfaceAgricoleBio($measureType, $metadata);

                break;
            case CouvertureFibre::getType():
                $this->loadMeasureCouvertureFibre($measureType, $metadata);

                break;
            case CreationEntreprise::getType():
                $this->loadMeasureCreationEntreprises($measureType, $metadata);

                break;
            case QuartierReconqueteRepublicaine::getType():
                $this->loadMeasureWithEmptyPayload(QuartierReconqueteRepublicaine::class, $measureType, $metadata);

                break;
            case EmploisFrancs::getType():
                $this->loadMeasureWithEmptyPayload(EmploisFrancs::class, $measureType, $metadata);

                break;
            case MaisonServiceAccueilPublic::getType():
                $this->loadMeasureWithEmptyPayload(MaisonServiceAccueilPublic::class, $measureType, $metadata);

                break;
            case PassCulture::getType():
                $this->loadMeasureWithEmptyPayload(PassCulture::class, $measureType, $metadata);

                break;
            case PrimeConversionAutomobile::getType():
                $this->loadMeasurePrimeConversionAutomobile($measureType, $metadata);

                break;
            case SuppressionTaxeHabitation::getType():
                $this->loadMeasureSuppressionTaxeHabitation($measureType, $metadata);

                break;
        }
    }

    private function findMeasure(City $city, MeasureType $type): ?Measure
    {
        return $this->measureRepository->findOneByCityAndType($city, $type);
    }

    private function loadMeasureBaisseNombreChomeurs(MeasureType $measureType, array $metadata): void
    {
        $inseeCode = $metadata['insee_code'];
        $baisseVille = $metadata[BaisseNombreChomeurs::KEY_BAISSE_VILLE];
        $baisseDepartement = $metadata[BaisseNombreChomeurs::KEY_BAISSE_DEPARTEMENT];

        if (empty($inseeCode)) {
            return;
        }

        $city = $this->findCity($inseeCode);

        if (!$city) {
            $this->io->text("No city found for insee_code \"$inseeCode\". Skipping.");

            return;
        }

        foreach (array_keys(BaisseNombreChomeurs::getKeys()) as $key) {
            if (0 !== \strlen($metadata[$key]) && !is_numeric($metadata[$key])) {
                $this->io->text("If defined, key \"$key\" should be a number (insee_code: \"$inseeCode\"). Skipping.");

                return;
            }
        }

        if (0 >= $baisseVille) {
            $baisseVille = null;
        }

        if (0 >= $baisseDepartement) {
            $baisseDepartement = null;
        }

        if (!$baisseVille && !$baisseDepartement) {
            return;
        }

        if ($measure = $this->findMeasure($city, $measureType)) {
            $measure->setPayload(BaisseNombreChomeurs::createPayload($baisseVille, $baisseDepartement));

            return;
        }

        $this->em->persist(BaisseNombreChomeurs::create($city, $measureType, $baisseVille, $baisseDepartement));
    }

    private function loadMeasureChequeEnergie(MeasureType $measureType, array $metadata): void
    {
        $inseeCode = $metadata['insee_code'];
        $nombreBeneficiaires = $metadata[ChequeEnergie::KEY_NOMBRE_BENEFICIAIRES];

        if (empty($inseeCode)) {
            return;
        }

        $city = $this->findCity($inseeCode);

        if (!$city) {
            $this->io->text("No city found for insee_code \"$inseeCode\". Skipping.");

            return;
        }

        if (0 === \strlen($nombreBeneficiaires) || !is_numeric($nombreBeneficiaires)) {
            $this->io->text(sprintf(
                'Key "%s" is required and should be a number (insee_code: "%s"). Skipping.',
                ChequeEnergie::KEY_NOMBRE_BENEFICIAIRES,
                $inseeCode
            ));

            return;
        }

        if ($measure = $this->findMeasure($city, $measureType)) {
            $measure->setPayload(ChequeEnergie::createPayload($nombreBeneficiaires));

            return;
        }

        $this->em->persist(ChequeEnergie::create($city, $measureType, $nombreBeneficiaires));
    }

    private function loadMeasureConversionSurfaceAgricoleBio(MeasureType $measureType, array $metadata): void
    {
        $inseeCode = $metadata['insee_code'];
        $hectaresBio = $metadata[ConversionSurfaceAgricoleBio::KEY_HECTARES_BIO];
        $progression = rtrim($metadata[ConversionSurfaceAgricoleBio::KEY_PROGRESSION], '%');

        if (empty($inseeCode)) {
            return;
        }

        $city = $this->findCity($inseeCode);

        if (!$city) {
            $this->io->text("No city found for insee_code \"$inseeCode\". Skipping.");

            return;
        }

        if (
            0 === \strlen($metadata[ConversionSurfaceAgricoleBio::KEY_HECTARES_BIO])
            || !is_numeric($metadata[ConversionSurfaceAgricoleBio::KEY_HECTARES_BIO])
        ) {
            $this->io->text(sprintf(
                'Key "%s" is required and should be a number (insee_code: "%s"). Skipping.',
                ConversionSurfaceAgricoleBio::KEY_HECTARES_BIO,
                $inseeCode
            ));

            return;
        }

        if (0 === \strlen($metadata[ConversionSurfaceAgricoleBio::KEY_PROGRESSION])) {
            $this->io->text(sprintf(
                'Key "%s" is required (insee_code: "%s"). Skipping.',
                ConversionSurfaceAgricoleBio::KEY_PROGRESSION,
                $inseeCode
            ));

            return;
        }

        if (0 >= $progression) {
            return;
        }

        if ($measure = $this->findMeasure($city, $measureType)) {
            $measure->setPayload(ConversionSurfaceAgricoleBio::createPayload($hectaresBio, $progression));

            return;
        }

        $this->em->persist(ConversionSurfaceAgricoleBio::create($city, $measureType, $hectaresBio, $progression));
    }

    private function loadMeasureCouvertureFibre(MeasureType $measureType, array $metadata): void
    {
        $inseeCode = $metadata['insee_code'];
        $nombreLocauxRaccordesVille = $metadata[CouvertureFibre::KEY_NOMBRE_LOCAUX_RACCORDES_VILLE];
        $hausseDepuis2017Ville = $metadata[CouvertureFibre::KEY_HAUSSE_DEPUIS_2017_VILLE];
        $nombreLocauxRaccordesDepartement = $metadata[CouvertureFibre::KEY_NOMBRE_LOCAUX_RACCORDES_DEPARTEMENT];
        $hausseDepuis2017Departement = $metadata[CouvertureFibre::KEY_HAUSSE_DEPUIS_2017_DEPARTEMENT];
        $progression = $metadata['progression'];

        if (empty($inseeCode)) {
            return;
        }

        $city = $this->findCity($inseeCode);

        if (!$city) {
            $this->io->text("No city found for insee_code \"$inseeCode\". Skipping.");

            return;
        }

        foreach (array_keys(CouvertureFibre::getKeys()) as $key) {
            if (0 !== \strlen($metadata[$key]) && !is_numeric($metadata[$key])) {
                $this->io->text("If defined, key \"$key\" should be a number (insee_code: \"$inseeCode\"). Skipping.");

                return;
            }
        }

        if (100 >= $hausseDepuis2017Ville || 0 > $progression) {
            $hausseDepuis2017Ville = null;
            $nombreLocauxRaccordesVille = null;
        }

        if (100 >= $hausseDepuis2017Departement) {
            $hausseDepuis2017Departement = null;
            $nombreLocauxRaccordesDepartement = null;
        }

        if (!$hausseDepuis2017Ville && !$hausseDepuis2017Departement) {
            return;
        }

        if ($measure = $this->findMeasure($city, $measureType)) {
            $measure->setPayload(CouvertureFibre::createPayload(
                $nombreLocauxRaccordesVille,
                $hausseDepuis2017Ville,
                $nombreLocauxRaccordesDepartement,
                $hausseDepuis2017Departement
            ));

            return;
        }

        $this->em->persist(CouvertureFibre::create(
            $city,
            $measureType,
            $nombreLocauxRaccordesVille,
            $hausseDepuis2017Ville,
            $nombreLocauxRaccordesDepartement,
            $hausseDepuis2017Departement
        ));
    }

    private function loadMeasureCreationEntreprises(MeasureType $measureType, array $metadata): void
    {
        $inseeCode = $metadata['insee_code'];
        $entreprises = $metadata[CreationEntreprise::KEY_ENTREPRISES];
        $microEntreprises = $metadata[CreationEntreprise::KEY_MICRO_ENTREPRISES];

        if (empty($inseeCode)) {
            return;
        }

        $city = $this->findCity($inseeCode);

        if (!$city) {
            $this->io->text("No city found for insee_code \"$inseeCode\". Skipping.");

            return;
        }

        if (0 === \strlen($entreprises) || !is_numeric($entreprises)) {
            $this->io->text(sprintf(
                'Key "%s" is required and should be a number (insee_code: "%s"). Skipping.',
                CreationEntreprise::KEY_ENTREPRISES,
                $inseeCode
            ));

            return;
        }

        if (0 !== \strlen($microEntreprises) && !is_numeric($microEntreprises)) {
            $this->io->text(sprintf(
                'If set, key "%s" should be a number (insee_code: "%s"). Skipping.',
                CreationEntreprise::KEY_MICRO_ENTREPRISES,
                $inseeCode
            ));

            return;
        }

        if (3 >= $entreprises) {
            return;
        }

        if (0 === $microEntreprises) {
            $microEntreprises = null;
        }

        if ($measure = $this->findMeasure($city, $measureType)) {
            $measure->setPayload(CreationEntreprise::createPayload($entreprises, $microEntreprises));

            return;
        }

        $this->em->persist(CreationEntreprise::create($city, $measureType, $entreprises, $microEntreprises));
    }

    private function loadMeasurePrimeConversionAutomobile(MeasureType $measureType, array $metadata): void
    {
        $inseeCode = $metadata['insee_code'];
        $nombreBeneficiaires = $metadata[PrimeConversionAutomobile::KEY_NOMBRE_BENEFICIAIRES];
        $montantMoyen = $metadata[PrimeConversionAutomobile::KEY_MONTANT_MOYEN];

        if (empty($inseeCode)) {
            return;
        }

        $city = $this->findCity($inseeCode);

        if (!$city) {
            $this->io->text("No city found for insee_code \"$inseeCode\". Skipping.");

            return;
        }

        foreach (PrimeConversionAutomobile::getKeys() as $key => $required) {
            if ($required && (0 === \strlen($metadata[$key]) || !is_numeric($metadata[$key]))) {
                $this->io->text("Key \"$key\" is required and should be a number (insee_code: \"$inseeCode\"). Skipping.");

                return;
            }
        }

        if ($measure = $this->findMeasure($city, $measureType)) {
            $measure->setPayload(PrimeConversionAutomobile::createPayload($nombreBeneficiaires, $montantMoyen));

            return;
        }

        $this->em->persist(PrimeConversionAutomobile::create($city, $measureType, $nombreBeneficiaires, $montantMoyen));
    }

    private function loadMeasureSuppressionTaxeHabitation(MeasureType $measureType, array $metadata): void
    {
        $inseeCode = $metadata['insee_code'];
        $nombreFoyers = $metadata[SuppressionTaxeHabitation::KEY_NOMBRE_FOYERS];
        $baisse2018 = $metadata[SuppressionTaxeHabitation::KEY_BAISSE_2018];
        $baisse2019 = $metadata[SuppressionTaxeHabitation::KEY_BAISSE_2019];
        $baisseTotal = $metadata[SuppressionTaxeHabitation::KEY_BAISSE_TOTAL];

        if (empty($inseeCode)) {
            return;
        }

        $city = $this->findCity($inseeCode);

        if (!$city) {
            $this->io->text("No city found for insee_code \"$inseeCode\". Skipping.");

            return;
        }

        foreach (SuppressionTaxeHabitation::getKeys() as $key => $required) {
            if ($required && (0 === \strlen($metadata[$key]) || !is_numeric($metadata[$key]))) {
                $this->io->text("Key \"$key\" is required and should be a number (insee_code: \"$inseeCode\"). Skipping.");

                return;
            }
        }

        if ($measure = $this->findMeasure($city, $measureType)) {
            $measure->setPayload(SuppressionTaxeHabitation::createPayload(
                $nombreFoyers,
                $baisse2018,
                $baisse2019,
                $baisseTotal
            ));

            return;
        }

        $this->em->persist(SuppressionTaxeHabitation::create($city, $measureType, $nombreFoyers, $baisse2018, $baisse2019, $baisseTotal));
    }

    private function loadMeasureWithEmptyPayload(string $measureClass, MeasureType $type, array $metadata): void
    {
        $inseeCode = $metadata['insee_code'];

        if (empty($inseeCode)) {
            return;
        }

        $city = $this->findCity($inseeCode);

        if (!$city) {
            $this->io->text("No city found for insee_code \"$inseeCode\". Skipping.");

            return;
        }

        if ($this->findMeasure($city, $type)) {
            return;
        }

        $this->em->persist($measureClass::createMeasure($city, $type));
    }
}
