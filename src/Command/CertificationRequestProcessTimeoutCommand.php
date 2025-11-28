<?php

declare(strict_types=1);

namespace App\Command;

use App\Adherent\Certification\CertificationRequestRefuseCommand;
use App\Entity\CertificationRequest;
use App\Repository\CertificationRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:certification-request:process-timeout',
    description: 'Refuse unprocessed Certification Requests.',
)]
class CertificationRequestProcessTimeoutCommand extends Command
{
    private $em;
    private $certificationRequestRepository;

    public function __construct(
        EntityManagerInterface $em,
        CertificationRequestRepository $certificationRequestRepository,
    ) {
        $this->em = $em;
        $this->certificationRequestRepository = $certificationRequestRepository;

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
        $certificationRequests = $this->certificationRequestRepository->findPending($createdBefore);

        foreach ($certificationRequests as $certificationRequest) {
            $this->processTimeout($certificationRequest);

            $this->em->flush();
        }

        return self::SUCCESS;
    }

    private function processTimeout(CertificationRequest $certificationRequest): void
    {
        $certificationRequest->refuse(CertificationRequestRefuseCommand::REFUSAL_REASON_PROCESS_TIMEOUT);
        $certificationRequest->process();
    }
}
