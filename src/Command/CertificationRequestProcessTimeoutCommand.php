<?php

namespace App\Command;

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

    public function __construct(
        EntityManagerInterface $em,
        CertificationRequestRepository $certificationRequestRepository
    ) {
        $this->em = $em;
        $this->certificationRequestRepository = $certificationRequestRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('interval', null, InputOption::VALUE_REQUIRED, 'Interval in days (default: 14)', 14)
            ->setDescription('Refuse unprocessed Certification Requests.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $interval = sprintf('-%d day', (int) $input->getOption('interval'));

        $createdBefore = new \DateTime('now');
        $createdBefore->add(\DateInterval::createFromDateString($interval));

        /** @var CertificationRequest[]|iterable $certificationRequests */
        $certificationRequests = $this->certificationRequestRepository->findPending($createdBefore);

        foreach ($certificationRequests as $certificationRequest) {
            $this->processTimeout($certificationRequest);

            $this->em->flush();
        }
    }

    private function processTimeout(CertificationRequest $certificationRequest): void
    {
        $certificationRequest->refuse(CertificationRequestRefuseCommand::REFUSAL_REASON_PROCESS_TIMEOUT);
        $certificationRequest->process();
    }
}
