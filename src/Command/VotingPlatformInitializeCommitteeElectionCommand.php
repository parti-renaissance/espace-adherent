<?php

namespace App\Command;

use App\Entity\CommitteeElection;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Repository\CommitteeRepository;
use App\Repository\VotingPlatform\DesignationRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class VotingPlatformInitializeCommitteeElectionCommand extends Command
{
    protected static $defaultName = 'app:voting-platform:initialize-committee-election';

    /** @var DesignationRepository */
    private $designationRepository;
    /** @var SymfonyStyle */
    private $io;
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var CommitteeRepository */
    private $committeeRepository;

    protected function configure()
    {
        $this->setDescription('Configure Voting Platform: initialize committee elections');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = new \DateTime();

        $designations = $this->designationRepository->getIncomingCandidacyDesignations($date);

        $this->io->progressStart();

        foreach ($designations as $designation) {
            if (DesignationTypeEnum::COMMITTEE_ADHERENT === $designation->getType()) {
                $this->configureCommitteeElections($designation);
            } else {
                $this->io->error(sprintf('Unhandled designation type "%s"', $designation->getType()));
            }
        }

        $this->io->progressFinish();
    }

    private function configureCommitteeElections(Designation $designation): void
    {
        while ($committees = $this->committeeRepository->findAllWithoutStartedElection($designation)) {
            foreach ($committees as $committee) {
                $this->io->progressAdvance();

                $committee->addCommitteeElection(new CommitteeElection($designation));
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

            $designation = $this->entityManager->merge($designation);
        }
    }

    /** @required */
    public function setDesignationRepository(DesignationRepository $designationRepository): void
    {
        $this->designationRepository = $designationRepository;
    }

    /** @required */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /** @required */
    public function setCommitteeRepository(CommitteeRepository $committeeRepository): void
    {
        $this->committeeRepository = $committeeRepository;
    }
}
