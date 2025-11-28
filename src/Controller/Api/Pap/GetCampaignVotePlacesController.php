<?php

declare(strict_types=1);

namespace App\Controller\Api\Pap;

use App\Entity\Pap\Campaign;
use App\Security\Voter\ScopeVisibilityVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GetCampaignVotePlacesController extends AbstractController
{
    public function __invoke(Campaign $campaign): array
    {
        $this->denyAccessUnlessGranted(ScopeVisibilityVoter::PERMISSION, $campaign);

        return $campaign->getVotePlaces()->toArray();
    }
}
