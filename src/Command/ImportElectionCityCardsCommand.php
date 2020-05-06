<?php

namespace App\Command;

use App\Entity\City;
use App\Entity\Election\CityCandidate;
use App\Entity\Election\CityCard;
use App\Entity\Election\CityManager;
use App\Entity\Election\CityPrevision;
use App\Repository\CityRepository;
use App\ValueObject\Genders;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportElectionCityCardsCommand extends Command
{
    protected static $defaultName = 'app:election-city-cards:import';

    private const BATCH_SIZE = 100;

    private const PRIORITIES_MAPPING = [
        'P1' => CityCard::PRIORITY_HIGH,
        'P2' => CityCard::PRIORITY_MEDIUM,
    ];

    private const GENDERS_MAP = [
        'H' => Genders::MALE,
        'F' => Genders::FEMALE,
    ];

    private $storage;
    private $em;
    private $cityRepository;

    /**
     * @var SymfonyStyle|null
     */
    private $io;

    public function __construct(
        FilesystemInterface $storage,
        EntityManagerInterface $em,
        CityRepository $cityRepository
    ) {
        $this->storage = $storage;
        $this->em = $em;
        $this->cityRepository = $cityRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('filename', InputArgument::REQUIRED)
            ->setDescription('Import city cards')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em->beginTransaction();

        try {
            $this->loadFile($input, $output);

            $this->em->commit();
        } catch (\Exception $exception) {
            $this->em->rollback();

            throw $exception;
        }
    }

    private function loadFile(InputInterface $input, OutputInterface $output): void
    {
        $this->io->section('Starting import of city cards.');

        $csv = Reader::createFromStream($this->storage->readStream($input->getArgument('filename')));
        $csv->setHeaderOffset(0);

        $this->io->progressStart($total = $csv->count());

        $line = 0;
        $lineNumber = 2;
        foreach ($csv as $row) {
            $inseeCode = str_pad(trim($row['insee_code']), 5, '0', \STR_PAD_LEFT);
            $population = (int) trim($row['population']);
            $priority = trim($row['priority']);

            $firstCandidateName = trim($row['first_candidate_name']);
            $firstCandidateGender = trim($row['first_candidate_gender']);
            $firstCandidateProfile = trim($row['first_candidate_profile']);
            $firstCandidatePoliticalScheme = trim($row['first_candidate_political_scheme']);
            $firstCandidateInvestitureType = trim($row['first_candidate_investiture_type']);
            $preparationPrevisionName = trim($row['preparation_prevision_name']);
            $preparationPrevisionAlliances = trim($row['preparation_prevision_alliances']);
            $preparationPrevisionAllies = trim($row['preparation_prevision_allies']);
            $candidateOptionPrevisionName = trim($row['candidate_option_prevision_name']);
            $candidateOptionPrevisionAlliances = trim($row['candidate_option_prevision_alliances']);
            $candidateOptionPrevisionAllies = trim($row['candidate_option_prevision_allies']);
            $thirdOptionPrevisionName = trim($row['third_option_prevision_name']);
            $thirdOptionPrevisionAlliances = trim($row['third_option_prevision_alliances']);
            $thirdOptionPrevisionAllies = trim($row['third_option_prevision_allies']);
            $risk = trim($row['risk']);
            $dissensus = trim($row['dissensus']);
            $headquartersManagerName = trim($row['headquarters_manager']);
            $politicalManagerName = trim($row['political_manager']);
            $taskForceManagerName = trim($row['task_force_manager']);

            /** @var City|null $city */
            $city = $this->cityRepository->findOneBy(['inseeCode' => $inseeCode]);

            if (!$city) {
                $this->io->text("No City found in database for inseeCode: \"$inseeCode\". (line $lineNumber)");

                continue;
            }

            if (!\array_key_exists($priority, self::PRIORITIES_MAPPING)) {
                $this->io->text("\"$priority\" is not a valid priority. (line $lineNumber)");

                continue;
            }

            $cityCard = new CityCard(
                $city,
                $population > 0 ? $population : null,
                self::PRIORITIES_MAPPING[$priority],
                !empty($risk)
            );

            if (!empty($firstCandidateName)) {
                $cityCard->setFirstCandidate(new CityCandidate(
                    $firstCandidateName,
                    self::GENDERS_MAP[$firstCandidateGender] ?? null,
                    null,
                    null,
                    !empty($firstCandidateProfile) ? $firstCandidateProfile : null,
                    !empty($firstCandidateInvestitureType) ? $firstCandidateInvestitureType : null,
                    !empty($firstCandidatePoliticalScheme) ? $firstCandidatePoliticalScheme : null,
                    null
                ));
            }

            if (
                !empty($preparationPrevisionName)
                || !empty($preparationPrevisionAlliances)
                || !empty($preparationPrevisionAllies)
            ) {
                $cityCard->setPreparationPrevision($this->createCityPrevision(
                    $preparationPrevisionName,
                    $preparationPrevisionAlliances,
                    $preparationPrevisionAllies
                ));
            }

            if (
                !empty($candidateOptionPrevisionName)
                || !empty($candidateOptionPrevisionAlliances)
                || !empty($candidateOptionPrevisionAllies)
            ) {
                $cityCard->setCandidateOptionPrevision($this->createCityPrevision(
                    $candidateOptionPrevisionName,
                    $candidateOptionPrevisionAlliances,
                    $candidateOptionPrevisionAllies
                ));
            }

            if (
                !empty($thirdOptionPrevisionName)
                || !empty($thirdOptionPrevisionAlliances)
                || !empty($thirdOptionPrevisionAllies)
            ) {
                $cityCard->setThirdOptionPrevision($this->createCityPrevision(
                    $thirdOptionPrevisionName,
                    $thirdOptionPrevisionAlliances,
                    $thirdOptionPrevisionAllies
                ));
            }

            if (!empty($headquartersManagerName)) {
                $cityCard->setHeadquartersManager($this->createCityManager($headquartersManagerName));
            }

            if (!empty($politicalManagerName)) {
                $cityCard->setPoliticManager($this->createCityManager($politicalManagerName));
            }

            if (!empty($taskForceManagerName)) {
                $cityCard->setTaskForceManager($this->createCityManager($taskForceManagerName));
            }

            $this->em->persist($cityCard);
            $this->em->flush();

            ++$lineNumber;
            ++$line;

            $this->io->progressAdvance();

            if (0 === ($line % self::BATCH_SIZE)) {
                $this->em->clear();
            }
        }

        $this->em->flush();
        $this->em->clear();

        $this->io->progressFinish();

        $this->io->success("$line city cards imported successfully !");
    }

    private function createCityPrevision(?string $name, ?string $alliances, ?string $allies): CityPrevision
    {
        return new CityPrevision(
            null,
            !empty($name) ? $name : null,
            !empty($alliances) ? $alliances : null,
            !empty($allies) ? $allies : null
        );
    }

    private function createCityManager(string $name): CityManager
    {
        return new CityManager($name);
    }
}
