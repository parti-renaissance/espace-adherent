<?php

namespace App\Adherent\Listener;

use App\Membership\Event\UserEmailEvent;
use App\Membership\UserEvents;
use App\Repository\DonatorRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateDonatorEmailSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly DonatorRepository $donatorRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_EMAIL_UPDATED => ['onAdherentEmailUpdate', -1],
        ];
    }

    public function onAdherentEmailUpdate(UserEmailEvent $event): void
    {
        $adherent = $event->getUser();
        $oldEmail = $event->getOldEmail();

        $this->donatorRepository->updateDonatorEmail($oldEmail, $adherent->getEmailAddress());
    }
}
