<?php

namespace App\Event\Handler;

use App\Adherent\Tag\TagEnum;
use App\Committee\CommitteeManager;
use App\Event\Command\SendCreationNotificationCommand;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\RenaissanceEventNotificationMessage;
use App\Repository\AdherentRepository;
use App\Repository\Event\EventRepository;
use App\Subscription\SubscriptionTypeEnum;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsMessageHandler]
class SendCreationNotificationCommandHandler
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly CommitteeManager $committeeManager,
        private readonly MailerService $transactionalMailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly AdherentRepository $adherentRepository,
    ) {
    }

    public function __invoke(SendCreationNotificationCommand $command): void
    {
        if (!$event = $this->eventRepository->findOneByUuid($command->getUuid())) {
            return;
        }

        if ($event->isCancelled()) {
            return;
        }

        if (!$event->isPublished()) {
            return;
        }

        if (!$event->sendInvitationEmail) {
            return;
        }

        $recipients = [];
        if ($committee = $event->getCommittee()) {
            $recipients = $this->adherentRepository->findInCommittee($committee, TagEnum::ADHERENT, SubscriptionTypeEnum::EVENT_EMAIL);
        } elseif ($zone = $event->getAssemblyZone()) {
            $recipients = $this->adherentRepository->findInAssembly($zone, TagEnum::ADHERENT, SubscriptionTypeEnum::EVENT_EMAIL);
        }

        if (!$recipients) {
            return;
        }

        foreach (array_chunk($recipients, MailerService::PAYLOAD_MAXSIZE) as $chunk) {
            $this->transactionalMailer->sendMessage(RenaissanceEventNotificationMessage::create(
                $chunk,
                $event->getAuthor(),
                $event,
                $this->urlGenerator->generate('vox_app', [], UrlGeneratorInterface::ABSOLUTE_URL).'evenements/'.$event->getSlug(),
            ));
        }
    }
}
