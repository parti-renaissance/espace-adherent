<?php

namespace App\Command;

use App\Repository\Pap\AddressRepository;
use App\Repository\Pap\CampaignRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PapAssociateActiveCampaignsToBuildingsCommand extends Command
{
    protected static $defaultName = 'app:pap:associate-campaigns';

    private ?SymfonyStyle $io = null;
    private ?CampaignRepository $campaignRepository = null;
    private ?EntityManagerInterface $entityManager = null;
    private ?AddressRepository $addressRepository = null;

    protected function configure()
    {
        $this
            ->setDescription('PAP: associate active campaign to building')
            ->addOption('interval', null, InputOption::VALUE_REQUIRED, 'Interval in minutes for campaign selection (1 min by default)', 1)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = new \DateTime();

        $campaigns = $this->campaignRepository->findUnassociatedCampaigns(
            $date->modify(sprintf('+%d minutes', (int) $input->getOption('interval')))
        );

        $this->io->progressStart(\count($campaigns));

        foreach ($campaigns as $campaign) {
            $this->addressRepository->associatedCampaign($campaign);

            $campaign->setAssociated(true);
            $this->entityManager->flush();

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        return 0;
    }

    /** @required */
    public function setCampaignRepository(CampaignRepository $campaignRepository): void
    {
        $this->campaignRepository = $campaignRepository;
    }

    /** @required */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /** @required */
    public function setAddressRepository(AddressRepository $addressRepository): void
    {
        $this->addressRepository = $addressRepository;
    }
}
