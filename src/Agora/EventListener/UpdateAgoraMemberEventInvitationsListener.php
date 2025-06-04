<?php

namespace App\Agora\EventListener;

use App\Agora\Command\InviteAdherentForAllFuturAgoraEventCommand;
use App\Agora\Command\InviteAgoraMembersForEventCommand;
use App\Agora\Command\RemoveAdherentForAllFuturAgoraEventCommand;
use App\Agora\Event\NewAgoraMemberEvent;
use App\Agora\Event\RemoveAgoraMemberEvent;
use App\Event\EventEvent;
use App\Event\EventVisibilityEnum;
use App\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateAgoraMemberEventInvitationsListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $messageBus)
    {
    }

    public function onEventCreated(EventEvent $event): void
    {
        if (EventVisibilityEnum::INVITATION_AGORA === $event->getEvent()->visibility) {
            $this->messageBus->dispatch(new InviteAgoraMembersForEventCommand($event->getEvent()->getUuid()));
        }
    }

    public function onPostMemberAdd(NewAgoraMemberEvent $event): void
    {
        $agoraMembership = $event->agoraMembership;
        $this->messageBus->dispatch(new InviteAdherentForAllFuturAgoraEventCommand($agoraMembership->adherent->getUuid(), $agoraMembership->agora->getId()));
    }

    public function onPostMemberRemove(RemoveAgoraMemberEvent $event): void
    {
        $agoraMembership = $event->agoraMembership;
        $this->messageBus->dispatch(new RemoveAdherentForAllFuturAgoraEventCommand($agoraMembership->adherent->getUuid(), $agoraMembership->agora->getId()));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::EVENT_CREATED => 'onEventCreated',
            NewAgoraMemberEvent::class => 'onPostMemberAdd',
            RemoveAgoraMemberEvent::class => 'onPostMemberRemove',
        ];
    }
}
