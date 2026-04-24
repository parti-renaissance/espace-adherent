<?php

declare(strict_types=1);

namespace App\Agora\EventListener;

use App\Agora\Event\NewAgoraMemberEvent;
use App\Agora\Event\RemoveAgoraMemberEvent;
use App\Event\Command\InviteAdherentForAllFutureInvitationEventsCommand;
use App\Event\Command\RemoveAdherentForAllFutureInvitationEventsCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateAgoraMemberEventInvitationsListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $messageBus)
    {
    }

    public function onPostMemberAdd(NewAgoraMemberEvent $event): void
    {
        $agoraMembership = $event->agoraMembership;
        $this->messageBus->dispatch(new InviteAdherentForAllFutureInvitationEventsCommand(
            $agoraMembership->adherent->getUuid(),
            agoraId: $agoraMembership->agora->getId(),
        ));
    }

    public function onPostMemberRemove(RemoveAgoraMemberEvent $event): void
    {
        $agoraMembership = $event->agoraMembership;
        $this->messageBus->dispatch(new RemoveAdherentForAllFutureInvitationEventsCommand(
            $agoraMembership->adherent->getUuid(),
            agoraId: $agoraMembership->agora->getId(),
        ));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NewAgoraMemberEvent::class => 'onPostMemberAdd',
            RemoveAgoraMemberEvent::class => 'onPostMemberRemove',
        ];
    }
}
