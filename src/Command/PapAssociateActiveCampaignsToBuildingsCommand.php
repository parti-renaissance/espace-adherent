<?php

declare(strict_types=1);

namespace App\Command;

use App\Pap\Exception\LocalCampaignException;
use App\Repository\Pap\AddressRepository;
use App\Repository\Pap\CampaignRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Service\Attribute\Required;

#[AsCommand(
    name: 'app:pap:associate-campaigns',
    description: 'PAP: associate active campaign to building',
)]
class PapAssociateActiveCampaignsToBuildingsCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ?SymfonyStyle $io = null;
    private ?CampaignRepository $campaignRepository = null;
    private ?EntityManagerInterface $entityManager = null;
    private ?AddressRepository $addressRepository = null;

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = new \DateTime();

        $campaigns = $this->campaignRepository->findUnassociatedCampaigns($date);

        $this->io->progressStart(\count($campaigns));

        foreach ($campaigns as $campaign) {
            try {
                $this->addressRepository->associatedCampaign($campaign);
            } catch (LocalCampaignException $e) {
                $this->logger->error($e->getMessage(), ['exception' => $e]);
                $this->io->progressAdvance();

                continue;
            }

            $campaign->setAssociated(true);
            $this->entityManager->flush();

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        return self::SUCCESS;
    }

    #[Required]
    public function setCampaignRepository(CampaignRepository $campaignRepository): void
    {
        $this->campaignRepository = $campaignRepository;
    }

    #[Required]
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    #[Required]
    public function setAddressRepository(AddressRepository $addressRepository): void
    {
        $this->addressRepository = $addressRepository;
    }
}
