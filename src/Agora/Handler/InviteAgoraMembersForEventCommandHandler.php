<?php

namespace App\Agora\Handler;

use App\Agora\Command\InviteAgoraMembersForEventCommand;
use App\Entity\Event\RegistrationStatusEnum;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationCommandHandler;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\AgoraEventInvitationMessage;
use App\Repository\Event\EventRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsMessageHandler]
class InviteAgoraMembersForEventCommandHandler
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly EventRegistrationCommandHandler $handler,
        private readonly MailerService $transactionalMailer,
        private readonly UrlGeneratorInterface $urlGenerator,
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

        if ($adherents) {
            $this->transactionalMailer->sendMessage(AgoraEventInvitationMessage::create(
                $event,
                $agora,
                $adherents,
                rtrim($this->urlGenerator->generate('vox_app', [], UrlGeneratorInterface::ABSOLUTE_URL), '/').'/evenements/'.$event->getSlug()
            ));
        }
    }
}
