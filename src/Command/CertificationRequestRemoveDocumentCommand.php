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

class CertificationRequestRemoveDocumentCommand extends Command
{
    protected static $defaultName = 'app:certification-request:remove-document';

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
            ->setDescription('Removes document of Certification Requests.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $createdBefore = new \DateTime(sprintf('-%d day', (int) $input->getOption('interval')));

        /** @var CertificationRequest[]|iterable $certificationRequests */
        $certificationRequests = $this->certificationRequestRepository->findDocumentsToDelete($createdBefore);

        foreach ($certificationRequests as $certificationRequest) {
            $this->removeDocument($certificationRequest);

            $this->em->flush();
        }
    }

    private function removeDocument(CertificationRequest $certificationRequest): void
    {
        $this->documentManager->removeDocument($certificationRequest);

        $certificationRequest->cleanOcr();
    }
}
