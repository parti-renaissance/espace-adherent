<?php

namespace App\Command;

use App\Adherent\Certification\CertificationRequestDocumentManager;
use App\Adherent\Certification\CertificationRequestRefuseCommand;
use App\Entity\CertificationRequest;
use App\Repository\CertificationRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CertificationRequestProcessTimeoutCommand extends Command
{
    protected static $defaultName = 'app:certification-request:process-timeout';

    private $em;
    private $certificationRequestRepository;
    private $documentManager;

    public function __construct(
        EntityManagerInterface $em,
        CertificationRequestRepository $certificationRequestRepository,
        CertificationRequestDocumentManager $documentManager
    ) {
        $this->em = $em;
        $this->certificationRequestRepository = $certificationRequestRepository;
        $this->documentManager = $documentManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('interval', null, InputOption::VALUE_REQUIRED, 'Interval in days (default: 14)', 14)
            ->setDescription('Removes document of unprocessed Certification Requests.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $interval = sprintf('-%s day', $input->getOption('interval'));
        /** @var CertificationRequest[]|iterable $certificationRequests */
        $certificationRequests = $this->certificationRequestRepository->findPending($interval);

        foreach ($certificationRequests as $certificationRequest) {
            $this->processTimeout($certificationRequest);

            $this->em->flush();
        }
    }

    private function processTimeout(CertificationRequest $certificationRequest): void
    {
        $certificationRequest->refuse(CertificationRequestRefuseCommand::REFUSAL_REASON_PROCESS_TIMEOUT);
        $certificationRequest->process();

        $this->documentManager->removeDocument($certificationRequest);
    }
}
