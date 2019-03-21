<?php

namespace AppBundle\Mailchimp\Synchronisation\EventListener;

use AppBundle\Committee\Event\CommitteeEventInterface;
use AppBundle\Committee\Event\FollowCommitteeEvent;
use AppBundle\Entity\Adherent;
use AppBundle\Mailchimp\Synchronisation\Command\AddAdherentToCommitteeStaticSegmentCommand;
use AppBundle\Mailchimp\Synchronisation\Command\AdherentChangeCommand;
use AppBundle\Mailchimp\Synchronisation\Command\RemoveAdherentFromCommitteeStaticSegmentCommand;
use AppBundle\Membership\UserEvent;
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

            UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE => 'onCommitteePrivilegeChange',
            UserEvents::USER_UPDATE_CITIZEN_PROJECT_PRIVILEGE => 'onCitizenProjectPrivilegeChange',
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

    public function onCitizenProjectPrivilegeChange(UserEvent $event): void
    {
        $this->dispatchMessage($event->getUser()->getUuid(), $event->getUser()->getEmailAddress());
    }

    public function onCommitteePrivilegeChange(CommitteeEventInterface $event): void
    {
        $adherent = $event->getAdherent();

        $this->dispatchMessage($adherent->getUuid(), $adherent->getEmailAddress());

        if (!$committee = $event->getCommittee()) {
            return;
        }

        if ($event instanceof FollowCommitteeEvent) {
            $message = new AddAdherentToCommitteeStaticSegmentCommand(
                $adherent->getUuid(),
                $committee->getUuid()
            );
        } else {
            $message = new RemoveAdherentFromCommitteeStaticSegmentCommand(
                $adherent->getUuid(),
                $committee->getUuid()
            );
        }

        $this->bus->dispatch($message);
    }

    public function onUserValidated(UserEvent $event): void
    {
        $adherent = $event->getUser();

        $this->dispatchMessage($adherent->getUuid(), $adherent->getEmailAddress());
    }

    private function dispatchMessage(UuidInterface $uuid, string $identifier, array $removedTags = []): void
    {
        $this->bus->dispatch(new AdherentChangeCommand($uuid, $identifier, $removedTags));
    }

    private function transformToArray(Adherent $adherent): array
    {
        return $this->normalizer->toArray(
            $adherent,
            SerializationContext::create()->setGroups(['adherent_change_diff'])
        );
    }
}
