<?php

declare(strict_types=1);

namespace App\EntityListener;

use App\Entity\Pap\Campaign;
use App\Repository\Pap\VotePlaceRepository;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class PapCampaignListener
{
    private VotePlaceRepository $votePlaceRepository;

    public function __construct(VotePlaceRepository $votePlaceRepository)
    {
        $this->votePlaceRepository = $votePlaceRepository;
    }

    public function preUpdate(Campaign $campaign, PreUpdateEventArgs $event): void
    {
        $vp = $this->votePlaceRepository->findByCampaign($campaign);
        if (array_diff($vp, $campaign->getVotePlaces()->toArray())
            || array_diff($campaign->getVotePlaces()->toArray(), $vp)) {
            $campaign->setAssociated(false);
        }
    }
}
