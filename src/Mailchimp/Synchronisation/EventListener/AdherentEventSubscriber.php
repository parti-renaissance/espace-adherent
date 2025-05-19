<?php

namespace App\Mailchimp\Synchronisation\EventListener;

use App\Adherent\Certification\Events as AdherentCertificationEvents;
use App\Committee\Event\CommitteeMembershipEventInterface;
use App\History\AdministratorActionEvent;
use App\History\AdministratorActionEvents;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommand;
use App\Mailchimp\Synchronisation\Command\AdherentDeleteCommand;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AdherentEventSubscriber implements EventSubscriberInterface
{
    private ?string $adherentEmail = null;

    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_VALIDATED => 'onUserValidated',

            UserEvents::USER_BEFORE_UPDATE => 'onBeforeUpdate',
            AdministratorActionEvents::ADMIN_USER_PROFILE_BEFORE_UPDATE => 'onBeforeUpdateFromAdmin',
            UserEvents::USER_UPDATED => 'onAfterUpdate',
            UserEvents::USER_UPDATE_INTERESTS => 'onAfterUpdate',
            UserEvents::USER_DELETED => 'onDelete',

            UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE => 'onCommitteePrivilegeChange',

            AdherentCertificationEvents::ADHERENT_CERTIFIED => 'onAfterUpdate',
            AdherentCertificationEvents::ADHERENT_UNCERTIFIED => 'onAfterUpdate',
        ];
    }

    public function onBeforeUpdate(UserEvent $event): void
    {
        $this->adherentEmail = $event->getAdherent()->getEmailAddress();
    }

    public function onBeforeUpdateFromAdmin(AdministratorActionEvent $event): void
    {
        $this->adherentEmail = $event->adherent->getEmailAddress();
    }

    public function onAfterUpdate(UserEvent $event): void
    {
        $adherent = $event->getAdherent();
        $this->dispatchAdherentChangeCommand($adherent->getUuid(), $this->adherentEmail ?? $adherent->getEmailAddress());
    }

    public function onCommitteePrivilegeChange(CommitteeMembershipEventInterface $event): void
    {
        $adherent = $event->getCommitteeMembership()->getAdherent();

        $this->dispatchAdherentChangeCommand($adherent->getUuid(), $adherent->getEmailAddress());
    }

    public function onDelete(UserEvent $event): void
    {
        $this->dispatch(new AdherentDeleteCommand($event->getAdherent()->getEmailAddress(), $event->getAdherent()->getId()));
    }

    public function onUserValidated(UserEvent $event): void
    {
        $adherent = $event->getAdherent();

        $this->dispatchAdherentChangeCommand($adherent->getUuid(), $adherent->getEmailAddress());
    }

    private function dispatchAdherentChangeCommand(UuidInterface $uuid, string $identifier): void
    {
        $this->dispatch(new AdherentChangeCommand($uuid, $identifier));
    }

    private function dispatch($command): void
    {
        $this->bus->dispatch($command);
    }
}
