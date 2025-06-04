<?php

namespace App\Agora\Handler;

use App\Agora\Command\InviteAgoraMembersForEventCommand;
use App\Agora\Notifier;
use App\Entity\Event\RegistrationStatusEnum;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationCommandHandler;
use App\Repository\Event\EventRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class InviteAgoraMembersForEventCommandHandler
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly EventRegistrationCommandHandler $handler,
        private readonly Notifier $notifier,
    ) {
    }

    public function __invoke(InviteAgoraMembersForEventCommand $command): void
    {
        if (!$event = $this->eventRepository->findOneByUuid($command->getUuid()->toString())) {
            return;
        }

        if (!$event->isAnnounceEnabled() || !($agora = $event->agora)) {
            return;
        }

        if (!$agora->published) {
            return;
        }

        $adherents = [];

        foreach ($agora->memberships as $membership) {
            if (!$adherent = $membership->adherent) {
                continue;
            }

            if ($this->handler->handle(new EventRegistrationCommand($event, $adherent, RegistrationStatusEnum::INVITED), false)) {
                $adherents[] = $adherent;
            }
        }

        $this->notifier->sendEventInvitation($event, $adherents);
    }
}
