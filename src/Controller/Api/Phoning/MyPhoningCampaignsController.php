<?php

namespace App\Controller\Api\Phoning;

use App\Repository\Phoning\CampaignRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;

class MyPhoningCampaignsController extends AbstractController
{
    public function __invoke(CampaignRepository $campaignRepository, UserInterface $adherent): array
    {
        return $campaignRepository->findForAdherent($adherent);
    }
}
