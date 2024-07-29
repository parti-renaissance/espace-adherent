<?php

namespace App\Command;

use App\Adherent\Certification\CertificationAuthorityManager;
use App\Adherent\Certification\CertificationRequestRefuseCommand;
use App\Entity\CertificationRequest;
use App\Repository\CertificationRequestRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:certification-request:process-pre-refused',
    description: 'Switch pre-refused certification requests to refused status.',
)]
class CertificationRequestProcessPreRefusedCommand extends Command
{
    private $certificationRequestRepository;
    private $certificationAuthorityManager;

    public function __construct(
        CertificationRequestRepository $certificationRequestRepository,
        CertificationAuthorityManager $certificationAuthorityManager
    ) {
        $this->certificationRequestRepository = $certificationRequestRepository;
        $this->certificationAuthorityManager = $certificationAuthorityManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('interval', null, InputOption::VALUE_REQUIRED, 'Interval in hours (default: 24)', 24)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $interval = \sprintf('-%d hour', (int) $input->getOption('interval'));

        $createdBefore = new \DateTime('now');
        $createdBefore->add(\DateInterval::createFromDateString($interval));

        /** @var CertificationRequest[]|iterable $certificationRequests */
        $certificationRequests = $this->certificationRequestRepository->findPreRefused($createdBefore);

        foreach ($certificationRequests as $certificationRequest) {
            $this->processPreRefused($certificationRequest);
        }

        return self::SUCCESS;
    }

    private function processPreRefused(CertificationRequest $certificationRequest): void
    {
        $certificationRequestRefuseCommand = new CertificationRequestRefuseCommand($certificationRequest);
        $certificationRequestRefuseCommand->setReason($certificationRequest->getOcrResult());

        $this->certificationAuthorityManager->refuse($certificationRequestRefuseCommand);
    }
}
