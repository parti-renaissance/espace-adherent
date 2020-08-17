<?php

namespace App\Mailchimp\Synchronisation\EventListener;

use App\AdherentMessage\Command\CreateStaticSegmentCommand;
use App\AdherentMessage\StaticSegmentInterface;
use App\CitizenProject\CitizenProjectFollowerChangeEvent;
use App\Committee\Event\CommitteeEventInterface;
use App\Committee\Event\FollowCommitteeEvent;
use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Events;
use App\Mailchimp\Synchronisation\Command\AddAdherentToStaticSegmentCommand;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommand;
use App\Mailchimp\Synchronisation\Command\AdherentDeleteCommand;
use App\Mailchimp\Synchronisation\Command\RemoveAdherentFromStaticSegmentCommand;
use App\Membership\UserEvent;
use App\Membership\UserEvents;
use App\TerritorialCouncil\Event\MembershipEvent;
use App\TerritorialCouncil\Events as TerritorialCouncilEvents;
use App\Utils\ArrayUtils;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializationContext;
use Ramsey\Uuid\Uuid;
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
            UserEvents::USER_DELETED => 'onDelete',

            UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE => 'onCommitteePrivilegeChange',
            UserEvents::USER_UPDATE_CITIZEN_PROJECT_PRIVILEGE => 'onCitizenProjectPrivilegeChange',

            Events::CITIZEN_PROJECT_FOLLOWER_ADDED => 'onCitizenProjectMembershipCreation',
            Events::CITIZEN_PROJECT_FOLLOWER_REMOVED => 'onCitizenProjectMembershipDeletion',

            TerritorialCouncilEvents::TERRITORIAL_COUNCIL_MEMBERSHIP_CREAT => 'onTerritorialCouncilMembershipCreation',
            TerritorialCouncilEvents::TERRITORIAL_COUNCIL_MEMBERSHIP_REMOVE => 'onTerritorialCouncilMembershipDeletion',
        ];
    }

    public function onBeforeUpdate(UserEvent $event): void
    {
        $this->before = $this->transformToArray($event->getUser());
    }

    public function onCitizenProjectMembershipCreation(CitizenProjectFollowerChangeEvent $event): void
    {
        $this->dispatchAddAdherentToStaticSegmentCommand($event->getFollower(), $event->getCitizenProject());
    }

    public function onCitizenProjectMembershipDeletion(CitizenProjectFollowerChangeEvent $event): void
    {
        $this->dispatchRemoveAdherentFromStaticSegmentCommand($event->getFollower(), $event->getCitizenProject());
    }

    public function onTerritorialCouncilMembershipCreation(MembershipEvent $event): void
    {
        $this->dispatchAddAdherentToStaticSegmentCommand($event->getAdherent(), $event->getTerritorialCouncil());
    }

    public function onTerritorialCouncilMembershipDeletion(MembershipEvent $event): void
    {
        $this->dispatchRemoveAdherentFromStaticSegmentCommand($event->getAdherent(), $event->getTerritorialCouncil());
    }

    public function onAfterUpdate(UserEvent $event): void
    {
        $after = $this->transformToArray($adherent = $event->getUser());

        $changeFrom = ArrayUtils::arrayDiffRecursive($this->before, $after);
        $changeTo = ArrayUtils::arrayDiffRecursive($after, $this->before);

        if (isset($changeFrom['territorial_council_membership']) || isset($changeTo['territorial_council_membership'])) {
            $this->handleUpdateTerritorialMembership($adherent, $changeFrom, $changeTo);
            unset($changeFrom['territorial_council_membership'], $changeTo['territorial_council_membership']);
        }

        if ($changeFrom || $changeTo) {
            $this->dispatchAdherentChangeCommand(
                $adherent->getUuid(),
                $changeFrom['emailAddress'] ?? $adherent->getEmailAddress(),
                isset($changeFrom['referentTagCodes']) ? (array) $changeFrom['referentTagCodes'] : []
            );
        }
    }

    public function onCitizenProjectPrivilegeChange(UserEvent $event): void
    {
        $this->dispatchAdherentChangeCommand($event->getUser()->getUuid(), $event->getUser()->getEmailAddress());
    }

    public function onCommitteePrivilegeChange(CommitteeEventInterface $event): void
    {
        $adherent = $event->getAdherent();

        $this->dispatchAdherentChangeCommand($adherent->getUuid(), $adherent->getEmailAddress());

        if (!$committee = $event->getCommittee()) {
            return;
        }

        if ($event instanceof FollowCommitteeEvent) {
            $this->dispatchAddAdherentToStaticSegmentCommand($adherent, $committee);
        } else {
            $this->dispatchRemoveAdherentFromStaticSegmentCommand($adherent, $committee);
        }
    }

    public function onDelete(UserEvent $event): void
    {
        $this->dispatch(new AdherentDeleteCommand($event->getUser()->getEmailAddress()));
    }

    public function onUserValidated(UserEvent $event): void
    {
        $adherent = $event->getUser();

        $this->dispatchAdherentChangeCommand($adherent->getUuid(), $adherent->getEmailAddress());
    }

    private function transformToArray(Adherent $adherent): array
    {
        return $this->normalizer->toArray(
            $adherent,
            SerializationContext::create()->setGroups(['adherent_change_diff'])
        );
    }

    private function dispatchAdherentChangeCommand(
        UuidInterface $uuid,
        string $identifier,
        array $removedTags = []
    ): void {
        $this->dispatch(new AdherentChangeCommand($uuid, $identifier, $removedTags));
    }

    private function dispatchAddAdherentToStaticSegmentCommand(Adherent $adherent, StaticSegmentInterface $object): void
    {
        if (!$object->getMailchimpId()) {
            $this->dispatch(new CreateStaticSegmentCommand($object->getUuid(), \get_class($object)));
        }

        $this->dispatch(new AddAdherentToStaticSegmentCommand(
            $adherent->getUuid(),
            $object->getUuid(),
            \get_class($object)
        ));
    }

    private function dispatchRemoveAdherentFromStaticSegmentCommand(
        Adherent $adherent,
        StaticSegmentInterface $object
    ): void {
        if (!$object->getMailchimpId()) {
            $this->dispatch(new CreateStaticSegmentCommand($object->getUuid(), \get_class($object)));
        }

        $this->dispatch(new RemoveAdherentFromStaticSegmentCommand(
            $adherent->getUuid(),
            $object->getUuid(),
            \get_class($object)
        ));
    }

    private function dispatch($command): void
    {
        $this->bus->dispatch($command);
    }

    private function handleUpdateTerritorialMembership(Adherent $adherent, array $changeFrom, array $changeTo): void
    {
        $councilFrom = $councilTo = null;

        if (isset($changeFrom['territorial_council_membership']['territorial_council']['uuid'])) {
            $councilFrom = $changeFrom['territorial_council_membership']['territorial_council']['uuid'];
        }

        if (isset($changeTo['territorial_council_membership']['territorial_council']['uuid'])) {
            $councilTo = $changeTo['territorial_council_membership']['territorial_council']['uuid'];
        }

        // Remove from static segment
        if (($councilFrom && !$councilTo) || ($councilFrom && $councilTo && $councilFrom !== $councilTo)) {
            $this->dispatch(new RemoveAdherentFromStaticSegmentCommand($adherent->getUuid(), Uuid::fromString($councilFrom), TerritorialCouncil::class));
        }

        // Add to static segment
        if (($councilTo && !$councilFrom) || ($councilFrom && $councilTo && $councilFrom !== $councilTo)) {
            $this->dispatch(new AddAdherentToStaticSegmentCommand($adherent->getUuid(), Uuid::fromString($councilTo), TerritorialCouncil::class));
        }
    }
}
