<?php

namespace App\Mailchimp\Synchronisation\EventListener;

use App\Adherent\Certification\Events as AdherentCertificationEvents;
use App\AdherentMessage\Command\CreateStaticSegmentCommand;
use App\AdherentMessage\StaticSegmentInterface;
use App\Committee\Event\CommitteeEventInterface;
use App\Entity\Adherent;
use App\Events;
use App\Mailchimp\Synchronisation\Command\AddAdherentToStaticSegmentCommand;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommand;
use App\Mailchimp\Synchronisation\Command\AdherentDeleteCommand;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AdherentEventSubscriber implements EventSubscriberInterface
{
    private $adherentEmail;

    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
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
            Events::COMMITTEE_NEW_FOLLOWER => 'onCommitteePrivilegeChange',

            AdherentCertificationEvents::ADHERENT_CERTIFIED => 'onAfterUpdate',
            AdherentCertificationEvents::ADHERENT_UNCERTIFIED => 'onAfterUpdate',
        ];
    }

    public function onBeforeUpdate(UserEvent $event): void
    {
        $this->adherentEmail = $event->getUser()->getEmailAddress();
    }

    public function onAfterUpdate(UserEvent $event): void
    {
        $adherent = $event->getUser();
        $this->dispatchAdherentChangeCommand($adherent->getUuid(), $this->adherentEmail ?? $adherent->getEmailAddress());
    }

    public function onCommitteePrivilegeChange(CommitteeEventInterface $event): void
    {
        $adherent = $event->getAdherent();

        $this->dispatchAdherentChangeCommand($adherent->getUuid(), $adherent->getEmailAddress());
    }

    public function onDelete(UserEvent $event): void
    {
        $this->dispatch(new AdherentDeleteCommand($event->getUser()->getEmailAddress(), $event->getUser()->getId()));
    }

    public function onUserValidated(UserEvent $event): void
    {
        $adherent = $event->getUser();

        $this->dispatchAdherentChangeCommand($adherent->getUuid(), $adherent->getEmailAddress());
    }

    private function dispatchAdherentChangeCommand(UuidInterface $uuid, string $identifier): void
    {
        $this->dispatch(new AdherentChangeCommand($uuid, $identifier));
    }

    private function dispatchAddAdherentToStaticSegmentCommand(Adherent $adherent, StaticSegmentInterface $object): void
    {
        if (!$object->getMailchimpId()) {
            $this->dispatch(new CreateStaticSegmentCommand($object->getUuid(), $object::class));
        }

        $this->dispatch(new AddAdherentToStaticSegmentCommand(
            $adherent->getUuid(),
            $object->getUuid(),
            $object::class
        ));
    }

    private function dispatch($command): void
    {
        $this->bus->dispatch($command);
    }
}
