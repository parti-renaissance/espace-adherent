<?php

namespace App\Command;

use App\Adherent\Certification\CertificationRequestProcessCommand;
use App\Entity\CertificationRequest;
use App\Repository\CertificationRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class CertificationRequestRecomputeCommand extends Command
{
    protected static $defaultName = 'app:certification-request:recompute';

    private $certificationRequestRepository;
    private $em;
    private $bus;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        CertificationRequestRepository $certificationRequestRepository,
        EntityManagerInterface $em,
        MessageBusInterface $bus
    ) {
        $this->certificationRequestRepository = $certificationRequestRepository;
        $this->em = $em;
        $this->bus = $bus;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('date', null, InputOption::VALUE_REQUIRED, 'Certification request creation date (format: YYYY-MM-DD, default: now)', 'now')
            ->setDescription('Recompute certification requests for a given date.')
        ;
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = \DateTime::createFromFormat('Y-m-d', $input->getOption('date'));

        /** @var CertificationRequest[]|iterable $certificationRequests */
        $certificationRequests = $this->certificationRequestRepository->findAllForDate($date);

        $this->io->text(sprintf('Will recompute %d certification requests.', $total = \count($certificationRequests)));

        $this->io->progressStart($total);

        foreach ($certificationRequests as $certificationRequest) {
            $certificationRequest->resetOcr();

            $this->em->flush();

            $this->bus->dispatch(new CertificationRequestProcessCommand($certificationRequest->getUuid()));

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->text('Done.');

        return 0;
    }
}
