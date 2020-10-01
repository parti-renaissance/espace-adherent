<?php

namespace App\Command;

use App\Adherent\Certification\CertificationRequestDocumentManager;
use App\Entity\CertificationRequest;
use App\Repository\CertificationRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CertificationRequestProcessPreApprovedCommand extends Command
{
    protected static $defaultName = 'app:certification-request:process-pre-approved';

    private $em;
    private $certificationRequestRepository;

    public function __construct(
        EntityManagerInterface $em,
        CertificationRequestRepository $certificationRequestRepository,
        CertificationRequestDocumentManager $documentManager
    ) {
        $this->em = $em;
        $this->certificationRequestRepository = $certificationRequestRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('interval', null, InputOption::VALUE_REQUIRED, 'Interval in hours (default: 24)', 24)
            ->setDescription('Switch pre-approved certification requests to approved status.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $interval = sprintf('-%s hour', $input->getOption('interval'));
        /** @var CertificationRequest[]|iterable $certificationRequests */
        $certificationRequests = $this->certificationRequestRepository->findPreApproved($interval);

        foreach ($certificationRequests as $certificationRequest) {
            $this->processPreApproved($certificationRequest);

            $this->em->flush();
        }
    }

    private function processPreApproved(CertificationRequest $certificationRequest): void
    {
        $certificationRequest->approve();
        $certificationRequest->process();
    }
}
