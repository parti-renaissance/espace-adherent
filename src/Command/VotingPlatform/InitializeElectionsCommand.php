<?php

namespace App\Command\VotingPlatform;

use App\Entity\Committee;
use App\Entity\CommitteeElection;
use App\Entity\Instance\NationalCouncil\Election as NationalCouncilElection;
use App\Entity\TerritorialCouncil\Election;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Repository\CommitteeRepository;
use App\Repository\Instance\NationalCouncil\ElectionRepository;
use App\Repository\TerritorialCouncil\TerritorialCouncilRepository;
use App\Repository\VotingPlatform\DesignationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:voting-platform:step-1:initialize-elections',
    description: 'Voting Platform: step 1: initialize elections for committees, territorial councils, etc...',
)]
class InitializeElectionsCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly DesignationRepository $designationRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly CommitteeRepository $committeeRepository,
        private readonly TerritorialCouncilRepository $territorialCouncilRepository,
        private readonly ElectionRepository $nationalCouncilElectionRepository
    ) {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = new \DateTime();

        $designations = $this->designationRepository->getIncomingCandidacyDesignations($date);

        $this->io->progressStart();

        foreach ($designations as $designation) {
            if ($designation->isCommitteeTypes()) {
                $this->configureCommitteeElections($designation);
            } elseif ($designation->isCopolType()) {
                $this->configureTerritorialCouncilElections($designation);
            } elseif ($designation->isExecutiveOfficeType()) {
                $this->configureNationalCouncilElections($designation);
            }
        }

        $this->io->progressFinish();

        return self::SUCCESS;
    }

    private function configureCommitteeElections(Designation $designation): void
    {
        while ($committees = $this->committeeRepository->findAllWithoutStartedElection($designation)) {
            foreach ($committees as $committee) {
                $committee->setCurrentElection(new CommitteeElection($designation));

                $this->entityManager->flush();

                $this->io->progressAdvance();
            }

            $this->entityManager->clear(Committee::class);
            $this->entityManager->clear(CommitteeElection::class);
        }
    }

    private function configureTerritorialCouncilElections(Designation $designation): void
    {
        while ($councils = $this->territorialCouncilRepository->findAllWithoutStartedElection($designation)) {
            foreach ($councils as $council) {
                $council->setCurrentElection(new Election($designation));

                $this->entityManager->flush();

                $this->io->progressAdvance();
            }

            $this->entityManager->clear(TerritorialCouncil::class);
            $this->entityManager->clear(Election::class);
        }
    }

    private function configureNationalCouncilElections(Designation $designation): void
    {
        if (!$this->nationalCouncilElectionRepository->hasActive()) {
            $this->entityManager->persist(new NationalCouncilElection($designation));
            $this->entityManager->flush();
            $this->io->progressAdvance();
        }
    }
}
