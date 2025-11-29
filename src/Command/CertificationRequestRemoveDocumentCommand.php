<?php

declare(strict_types=1);

namespace App\Command;

use App\Adherent\Certification\CertificationRequestDocumentManager;
use App\Entity\CertificationRequest;
use App\Repository\CertificationRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:certification-request:remove-document',
    description: 'Removes document of Certification Requests.',
)]
class CertificationRequestRemoveDocumentCommand extends Command
{
    private $em;
    private $certificationRequestRepository;
    private $documentManager;

    public function __construct(
        EntityManagerInterface $em,
        CertificationRequestRepository $certificationRequestRepository,
        CertificationRequestDocumentManager $documentManager,
    ) {
        $this->em = $em;
        $this->certificationRequestRepository = $certificationRequestRepository;
        $this->documentManager = $documentManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('interval', null, InputOption::VALUE_REQUIRED, 'Interval in days (default: 14)', 14)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $createdBefore = new \DateTime(\sprintf('-%d day', (int) $input->getOption('interval')));

        /** @var CertificationRequest[]|iterable $certificationRequests */
        $certificationRequests = $this->certificationRequestRepository->findDocumentsToDelete($createdBefore);

        foreach ($certificationRequests as $certificationRequest) {
            $this->removeDocument($certificationRequest);

            $this->em->flush();
        }

        return self::SUCCESS;
    }

    private function removeDocument(CertificationRequest $certificationRequest): void
    {
        $this->documentManager->removeDocument($certificationRequest);

        $certificationRequest->cleanOcr();
    }
}
