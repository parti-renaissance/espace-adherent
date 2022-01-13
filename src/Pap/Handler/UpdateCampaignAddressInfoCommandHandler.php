<?php

namespace App\Pap\Handler;

use App\Entity\Pap\Campaign;
use App\Pap\Command\UpdateCampaignAddressInfoCommand;
use App\Repository\Pap\AddressRepository;
use App\Repository\Pap\CampaignRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UpdateCampaignAddressInfoCommandHandler implements MessageHandlerInterface
{
    private CampaignRepository $campaignRepository;
    private AddressRepository $addressRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        CampaignRepository $campaignRepository,
        AddressRepository $addressRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->campaignRepository = $campaignRepository;
        $this->addressRepository = $addressRepository;
        $this->entityManager = $entityManager;
    }

    public function __invoke(UpdateCampaignAddressInfoCommand $command): void
    {
        /** @var Campaign $campaign */
        if (!$campaign = $this->campaignRepository->findOneByUuid($command->getCampaignUuid())) {
            return;
        }

        $campaign->setNbAddresses($this->addressRepository->countByPapCampaign($campaign));
        $campaign->setNbVoters($this->addressRepository->countVotersByPapCampaign($campaign));

        $this->entityManager->flush();
    }
}
