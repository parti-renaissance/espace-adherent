<?php

namespace App\Command;

use App\Entity\ApplicationRequest\ApplicationRequest;
use App\Entity\ApplicationRequest\RunningMateRequest;
use App\Entity\ApplicationRequest\VolunteerRequest;
use App\Referent\ReferentTagManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ApplicationRequestsUpdateMissingTags extends Command
{
    protected static $defaultName = 'app:application-requests:update-missing-tags';

    private $entityManager;
    private $referentTagManager;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(EntityManagerInterface $entityManager, ReferentTagManager $referentTagManager)
    {
        $this->entityManager = $entityManager;
        $this->referentTagManager = $referentTagManager;

        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->text('Start updating referent tags of application requests.');

        $this->updateApplicationRequests(RunningMateRequest::class);
        $this->updateApplicationRequests(VolunteerRequest::class);

        $this->io->success('Done');
    }

    private function updateApplicationRequests(string $entityClass): void
    {
        /** @var ApplicationRequest[]|array $applicationRequests */
        $applicationRequests = $this->entityManager->getRepository($entityClass)->findAll();

        $this->io->progressStart(\count($applicationRequests));

        foreach ($applicationRequests as $applicationRequest) {
            $this->io->progressAdvance();

            if (!$applicationRequest->getReferentTags()->isEmpty()) {
                continue;
            }

            $this->referentTagManager->assignApplicationRequestReferentTags($applicationRequest);
        }

        $this->entityManager->flush();

        $this->io->progressFinish();
    }
}
