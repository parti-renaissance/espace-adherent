<?php

namespace App\SendInBlue\EventListener;

use App\Entity\Adherent;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\SendInBlue\Command\AdherentDeleteCommand;
use App\SendInBlue\Command\AdherentSynchronisationCommand;
use App\Utils\ArrayUtils;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AdherentEventSubscriber implements EventSubscriberInterface
{
    private array $before = [];
    private NormalizerInterface $normalizer;
    private MessageBusInterface $bus;

    public function __construct(NormalizerInterface $normalizer, MessageBusInterface $bus)
    {
        $this->normalizer = $normalizer;
        $this->bus = $bus;
    }

    public static function getSubscribedEvents()
    {
        return [
            // Adherent creation
            UserEvents::USER_VALIDATED => 'onUserValidated',

            UserEvents::USER_BEFORE_UPDATE => 'onBeforeUpdate',
            UserEvents::USER_UPDATED => 'onAfterUpdate',
            UserEvents::USER_UPDATE_INTERESTS => 'onAfterUpdate',
            UserEvents::USER_UPDATE_SUBSCRIPTIONS => 'onAfterUpdate',
            UserEvents::USER_DELETED => 'onDelete',
        ];
    }

    public function onBeforeUpdate(UserEvent $event): void
    {
        $this->before = $this->transformToArray($event->getUser());
    }

    public function onAfterUpdate(UserEvent $event): void
    {
        $after = $this->transformToArray($adherent = $event->getUser());

        $changeFrom = ArrayUtils::arrayDiffRecursive($this->before, $after);
        $changeTo = ArrayUtils::arrayDiffRecursive($after, $this->before);

        if ($changeFrom || $changeTo) {
            $this->dispatchAdherentChangeCommand(
                $adherent->getUuid(),
                $changeFrom['emailAddress'] ?? $adherent->getEmailAddress()
            );
        }
    }

    public function onDelete(UserEvent $event): void
    {
        $this->dispatch(new AdherentDeleteCommand($event->getUser()->getUuid()));
    }

    public function onUserValidated(UserEvent $event): void
    {
        $adherent = $event->getUser();

        $this->dispatchAdherentChangeCommand($adherent->getUuid(), $adherent->getEmailAddress());
    }

    private function transformToArray(Adherent $adherent): array
    {
        return $this->normalizer->normalize($adherent, null, ['groups' => ['adherent_change_diff']]);
    }

    private function dispatchAdherentChangeCommand(UuidInterface $uuid, string $identifier): void
    {
        $this->dispatch(new AdherentSynchronisationCommand($uuid, $identifier));
    }

    private function dispatch($command): void
    {
        $this->bus->dispatch($command);
    }
}
