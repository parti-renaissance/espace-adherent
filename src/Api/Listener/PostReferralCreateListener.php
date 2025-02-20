<?php

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Adherent\Referral\Notifier;
use App\Entity\Referral;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PostReferralCreateListener implements EventSubscriberInterface
{
    public function __construct(private readonly Notifier $notifier)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['onSubscribe', EventPriorities::POST_WRITE]];
    }

    public function onSubscribe(ViewEvent $viewEvent): void
    {
        $referral = $viewEvent->getControllerResult();

        if (!$referral instanceof Referral) {
            return;
        }

        if ($referral->isAdhesion()) {
            $this->notifier->sendAdhesionMessage($referral);
        }
    }
}
