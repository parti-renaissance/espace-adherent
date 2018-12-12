<?php

namespace AppBundle\Mailchimp\Synchronisation\EventListener;

use AppBundle\Entity\Adherent;
use AppBundle\Mailchimp\Synchronisation\Message\AdherentChangeMessage;
use AppBundle\Membership\UserCollectionEvent;
use AppBundle\Membership\UserEvent;
use AppBundle\Membership\UserEventInterface;
use AppBundle\Membership\UserEvents;
use AppBundle\Utils\ArrayUtils;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializationContext;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AdherentEventSubscriber implements EventSubscriberInterface
{
    private $before = [];
    private $normalizer;
    private $bus;

    public function __construct(ArrayTransformerInterface $normalizer, MessageBusInterface $bus)
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

            UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE => 'onPrivilegeChange',
            UserEvents::USER_UPDATE_CITIZEN_PROJECT_PRIVILEGE => 'onPrivilegeChange',
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
            $this->dispatchMessage(
                $adherent->getUuid(),
                $changeFrom['emailAddress'] ?? $adherent->getEmailAddress(),
                isset($changeFrom['referentTagCodes']) ? (array) $changeFrom['referentTagCodes'] : []
            );
        }
    }

    public function onPrivilegeChange(UserEventInterface $event): void
    {
        if ($event instanceof UserCollectionEvent) {
            foreach ($event->getUsers() as $user) {
                $this->dispatchMessage($user->getUuid(), $user->getEmailAddress());
            }
        } elseif ($event instanceof UserEvent) {
            $this->dispatchMessage($event->getUser()->getUuid(), $event->getUser()->getEmailAddress());
        }
    }

    public function onUserValidated(UserEvent $event): void
    {
        $adherent = $event->getUser();

        $this->dispatchMessage($adherent->getUuid(), $adherent->getEmailAddress());
    }

    private function dispatchMessage(UuidInterface $uuid, string $identifier, array $removedTags = []): void
    {
        $this->bus->dispatch(new AdherentChangeMessage($uuid, $identifier, $removedTags));
    }

    private function transformToArray(Adherent $adherent): array
    {
        return $this->normalizer->toArray($adherent, SerializationContext::create()->setGroups(['change_diff']));
    }
}
