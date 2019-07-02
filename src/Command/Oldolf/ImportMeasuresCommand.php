<?php

namespace AppBundle\Command\Oldolf;

use AppBundle\Entity\Oldolf\City;
use AppBundle\Entity\Oldolf\Measure;
use AppBundle\Oldolf\Measure\BaisseNombreChomeurs;
use AppBundle\Oldolf\Measure\ChequeEnergie;
use AppBundle\Oldolf\Measure\ConversionSurfaceAgricoleBio;
use AppBundle\Oldolf\Measure\CouvertureFibre;
use AppBundle\Oldolf\Measure\CreationEntreprise;
use AppBundle\Oldolf\Measure\CreationPoliceSecuriteQuotidien;
use AppBundle\Oldolf\Measure\EmploisFrancs;
use AppBundle\Oldolf\Measure\MaisonServiceAccueilPublic;
use AppBundle\Oldolf\Measure\PassCulture;
use AppBundle\Oldolf\Measure\PrimeConversionAutomobile;
use AppBundle\Oldolf\Measure\SuppressionTaxeHabitation;
use AppBundle\Oldolf\MeasureChoiceLoader;
use AppBundle\Repository\Oldolf\CityRepository;
use AppBundle\Repository\Oldolf\MeasureRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportMeasuresCommand extends AbstractImportCommand
{
    private const CSV_DIRECTORY = 'oldolf/measures';

    protected static $defaultName = 'app:oldolf:import-measures';

    private $measureRepository;
    private $measureFactory;

    protected function configure()
    {
        $this
            ->addArgument('type', InputArgument::OPTIONAL)
            ->setDescription('Import OLDOLF measures from CSV files')
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

        $this->io->title('OLDOLF measures import');

        $this->em->beginTransaction();

        if ($type) {
            $this->importMeasureType($type);
        } else {
            foreach ($this->measureFactory->getTypeChoices() as $type) {
                $this->importMeasureType($type);
            }
        }

        $this->em->commit();

        $this->io->success('OLDOLF measures imported successfully!');
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
            $this->loadMeasure($type, $row);

            $this->em->flush();

            $this->io->progressAdvance();
            ++$count;

            if (0 === ($count % self::BATCH_SIZE)) {
                $this->em->clear();
            }
        }

        $this->em->clear();

        $this->io->progressFinish();

        $this->io->comment("Processed $total measures of type \"$type\".");
    }

    private function loadMeasure(string $type, array $metadata): void
    {
        switch ($type) {
            case BaisseNombreChomeurs::getType():
                $this->loadMeasureBaisseNombreChomeurs($metadata);

                break;
            case ChequeEnergie::getType():
                $this->loadMeasureChequeEnergie($metadata);

                break;
            case ConversionSurfaceAgricoleBio::getType():
                $this->loadMeasureConversionSurfaceAgricoleBio($metadata);

                break;
            case CouvertureFibre::getType():
                $this->loadMeasureCouvertureFibre($metadata);

                break;
            case CreationEntreprise::getType():
                $this->loadMeasureCreationEntreprises($metadata);

                break;
            case CreationPoliceSecuriteQuotidien::getType():
                $this->loadMeasureWithEmptyPayload(CreationPoliceSecuriteQuotidien::class, $metadata);

                break;
            case EmploisFrancs::getType():
                $this->loadMeasureWithEmptyPayload(EmploisFrancs::class, $metadata);

                break;
            case MaisonServiceAccueilPublic::getType():
                $this->loadMeasureWithEmptyPayload(MaisonServiceAccueilPublic::class, $metadata);

                break;
            case PassCulture::getType():
                $this->loadMeasureWithEmptyPayload(PassCulture::class, $metadata);

                break;
            case PrimeConversionAutomobile::getType():
                $this->loadMeasurePrimeConversionAutomobile($metadata);

                break;
            case SuppressionTaxeHabitation::getType():
                $this->loadMeasureSuppressionTaxeHabitation($metadata);

                break;
            default:
                break;
        }
    }

    private function findMeasure(City $city, string $type): ?Measure
    {
        return $this->measureRepository->findOneByCityAndType($city, $type);
    }

    private function loadMeasureBaisseNombreChomeurs(array $metadata): void
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

        foreach (BaisseNombreChomeurs::getKeys() as $key => $required) {
            if ($required && (0 === \strlen($metadata[$key]) || !is_numeric($metadata[$key]))) {
                $this->io->text("Key \"$key\" is required and should be a number (insee_code: \"$inseeCode\"). Skipping.");

                return;
            }
        }

        if ($measure = $this->findMeasure($city, BaisseNombreChomeurs::getType())) {
            $measure->setPayload(BaisseNombreChomeurs::createPayload($baisseVille, $baisseDepartement));

            return;
        }

        $this->em->persist(BaisseNombreChomeurs::create($city, $baisseVille, $baisseDepartement));
    }

    private function loadMeasureChequeEnergie(array $metadata): void
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

        if ($measure = $this->findMeasure($city, ChequeEnergie::getType())) {
            $measure->setPayload(ChequeEnergie::createPayload($nombreBeneficiaires));

            return;
        }

        $this->em->persist(ChequeEnergie::create($city, $nombreBeneficiaires));
    }

    private function loadMeasureConversionSurfaceAgricoleBio(array $metadata): void
    {
        $inseeCode = $metadata['insee_code'];
        $hectaresBio = $metadata[ConversionSurfaceAgricoleBio::KEY_HECTARES_BIO];
        $progression = $metadata[ConversionSurfaceAgricoleBio::KEY_PROGRESSION];

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

        if ($measure = $this->findMeasure($city, ConversionSurfaceAgricoleBio::getType())) {
            $measure->setPayload(ConversionSurfaceAgricoleBio::createPayload($hectaresBio, $progression));

            return;
        }

        $this->em->persist(ConversionSurfaceAgricoleBio::create($city, $hectaresBio, $progression));
    }

    private function loadMeasureCouvertureFibre(array $metadata): void
    {
        $inseeCode = $metadata['insee_code'];
        $nombreLocauxRaccordesVille = $metadata[CouvertureFibre::KEY_NOMBRE_LOCAUX_RACCORDES_VILLE];
        $hausseDepuis2017Ville = $metadata[CouvertureFibre::KEY_HAUSSE_DEPUIS_2017_VILLE];
        $nombreLocauxRaccordesDepartement = $metadata[CouvertureFibre::KEY_NOMBRE_LOCAUX_RACCORDES_DEPARTEMENT];
        $hausseDepuis2017Departement = $metadata[CouvertureFibre::KEY_HAUSSE_DEPUIS_2017_DEPARTEMENT];

        if (empty($inseeCode)) {
            return;
        }

        $city = $this->findCity($inseeCode);

        if (!$city) {
            $this->io->text("No city found for insee_code \"$inseeCode\". Skipping.");

            return;
        }

        foreach (CouvertureFibre::getKeys() as $key => $required) {
            if ($required && (0 === \strlen($metadata[$key]) || !is_numeric($metadata[$key]))) {
                $this->io->text("Key \"$key\" is required and should be a number (insee_code: \"$inseeCode\"). Skipping.");

                return;
            }
        }

        if ($measure = $this->findMeasure($city, CouvertureFibre::getType())) {
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
            $nombreLocauxRaccordesVille,
            $hausseDepuis2017Ville,
            $nombreLocauxRaccordesDepartement,
            $hausseDepuis2017Departement
        ));
    }

    private function loadMeasureCreationEntreprises(array $metadata): void
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

        if ($measure = $this->findMeasure($city, CreationEntreprise::getType())) {
            $measure->setPayload(CreationEntreprise::createPayload($entreprises, $microEntreprises));

            return;
        }

        $this->em->persist(CreationEntreprise::create($city, $entreprises, $microEntreprises));
    }

    private function loadMeasurePrimeConversionAutomobile(array $metadata): void
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

        if ($measure = $this->findMeasure($city, PrimeConversionAutomobile::getType())) {
            $measure->setPayload(PrimeConversionAutomobile::createPayload($nombreBeneficiaires, $montantMoyen));

            return;
        }

        $this->em->persist(PrimeConversionAutomobile::create($city, $nombreBeneficiaires, $montantMoyen));
    }

    private function loadMeasureSuppressionTaxeHabitation(array $metadata): void
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

        if ($measure = $this->findMeasure($city, SuppressionTaxeHabitation::getType())) {
            $measure->setPayload(SuppressionTaxeHabitation::createPayload(
                $nombreFoyers,
                $baisse2018,
                $baisse2019,
                $baisseTotal
            ));

            return;
        }

        $this->em->persist(SuppressionTaxeHabitation::create($city, $nombreFoyers, $baisse2018, $baisse2019, $baisseTotal));
    }

    private function loadMeasureWithEmptyPayload(string $measureClass, array $metadata): void
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

        if ($this->findMeasure($city, $measureClass::getType())) {
            return;
        }

        $this->em->persist($measureClass::createMeasure($city));
    }
}
