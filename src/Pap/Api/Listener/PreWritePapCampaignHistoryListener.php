<?php

namespace App\Pap\Api\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Adherent;
use App\Entity\Pap\CampaignHistory;
use App\OAuth\Model\DeviceApiUser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PreWritePapCampaignHistoryListener implements EventSubscriberInterface
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::VIEW => ['preWrite', EventPriorities::PRE_WRITE]];
    }

    public function preWrite(ViewEvent $event): void
    {
        $campaignHistory = $event->getControllerResult();

        if (!$campaignHistory instanceof CampaignHistory) {
            return;
        }

        if ($event->getRequest()->isMethod(Request::METHOD_PUT)
            && $campaignHistory->isFinishedStatus()) {
            $campaignHistory->setFinishAt(new \DateTime());
        }

        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof Adherent) {
            $campaignHistory->setQuestioner($user);
        } elseif ($user instanceof DeviceApiUser) {
            $campaignHistory->setDevice($user);
        }
    }
}
