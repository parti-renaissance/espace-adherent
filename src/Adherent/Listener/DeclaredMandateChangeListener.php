<?php

namespace App\Adherent\Listener;

use App\Adherent\DeclaredMandateHistoryHandler;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DeclaredMandateChangeListener implements EventSubscriberInterface
{
    private array $oldDeclaredMandates = [];

    public function __construct(private readonly DeclaredMandateHistoryHandler $declaredMandateHistoryHandler)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_BEFORE_UPDATE => 'beforeUpdate',
            UserEvents::USER_UPDATED => 'afterUpdate',
        ];
    }

    public function beforeUpdate(UserEvent $event): void
    {
        $this->oldDeclaredMandates = $event->getUser()->getMandates();
    }

    public function afterUpdate(UserEvent $event): void
    {
        $adherent = $event->getUser();
        $newDeclaredMandates = $adherent->getMandates();

        $addedMandates = array_diff($newDeclaredMandates, $this->oldDeclaredMandates);
        $removedMandates = array_diff($this->oldDeclaredMandates, $newDeclaredMandates);

        if ($addedMandates || $removedMandates) {
            $this->declaredMandateHistoryHandler->handle($adherent, $addedMandates, $removedMandates);
        }
    }
}
