<?php

declare(strict_types=1);

namespace App\Controller\Api\Phoning;

use App\Controller\EnMarche\VotingPlatform\AbstractController;
use App\Entity\Adherent;
use App\Repository\Phoning\CampaignRepository;

class CampaignsScoresController extends AbstractController
{
    public function __invoke(CampaignRepository $campaignRepository): array
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        return $campaignRepository->findForAdherent($adherent);
    }
}
