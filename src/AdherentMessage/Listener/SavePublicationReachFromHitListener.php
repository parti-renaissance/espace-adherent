<?php

declare(strict_types=1);

namespace App\AdherentMessage\Listener;

use App\AdherentMessage\Command\CreatePublicationReachFromAppCommand;
use App\JeMengage\Hit\Event\NewHitSavedEvent;
use App\JeMengage\Hit\TargetTypeEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SavePublicationReachFromHitListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $messageBus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [NewHitSavedEvent::class => 'onNewHitSavedEvent'];
    }

    public function onNewHitSavedEvent(NewHitSavedEvent $event): void
    {
        $hit = $event->hit;

        if (TargetTypeEnum::Publication !== $hit->objectType || !$hit->isImpression()) {
            return;
        }

        $this->messageBus->dispatch(new CreatePublicationReachFromAppCommand(
            $hit->objectId,
            $hit->adherent->getId(),
            $hit->getCreatedAt()
        ));
    }
}
