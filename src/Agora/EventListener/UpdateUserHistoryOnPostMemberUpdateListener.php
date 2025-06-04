<?php

namespace App\Agora\EventListener;

use App\Agora\Event\NewAgoraMemberEvent;
use App\Agora\Event\RemoveAgoraMemberEvent;
use App\History\UserActionHistoryHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateUserHistoryOnPostMemberUpdateListener implements EventSubscriberInterface
{
    public function __construct(private readonly UserActionHistoryHandler $userActionHistoryHandler)
    {
    }

    public function onPostMemberAdd(NewAgoraMemberEvent $event): void
    {
        $agoraMembership = $event->agoraMembership;
        $this->userActionHistoryHandler->createAgoraMembershipAdd($agoraMembership->adherent, $agoraMembership->agora);
    }

    public function onPostMemberRemove(RemoveAgoraMemberEvent $event): void
    {
        $agoraMembership = $event->agoraMembership;
        $this->userActionHistoryHandler->createAgoraMembershipRemove($agoraMembership->adherent, $agoraMembership->agora);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NewAgoraMemberEvent::class => 'onPostMemberAdd',
            RemoveAgoraMemberEvent::class => 'onPostMemberRemove',
        ];
    }
}
