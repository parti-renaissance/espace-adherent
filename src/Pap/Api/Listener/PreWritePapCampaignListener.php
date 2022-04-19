<?php

namespace App\Pap\Api\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Pap\Campaign;
use App\Repository\Pap\VotePlaceRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PreWritePapCampaignListener implements EventSubscriberInterface
{
    private VotePlaceRepository $votePlaceRepository;

    public function __construct(VotePlaceRepository $votePlaceRepository)
    {
        $this->votePlaceRepository = $votePlaceRepository;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::VIEW => ['preWrite', EventPriorities::PRE_VALIDATE]];
    }

    public function preWrite(ViewEvent $event): void
    {
        $campaign = $event->getControllerResult();

        if (!$campaign instanceof Campaign) {
            return;
        }

        if (Request::METHOD_PUT !== $event->getRequest()->getMethod()) {
            return;
        }

        $vp = $this->votePlaceRepository->findByCampaign($campaign);
        if (array_diff($vp, $campaign->getVotePlaces()->toArray())
            || array_diff($campaign->getVotePlaces()->toArray(), $vp)) {
            $campaign->setAssociated(false);
        }
    }
}
