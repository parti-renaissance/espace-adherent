<?php

declare(strict_types=1);

namespace App\Event\Handler;

use App\Adherent\Tag\TagEnum;
use App\Entity\Event\Event;
use App\Entity\Geo\Zone;
use App\Event\Command\SendCreationNotificationCommand;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\RenaissanceEventNotificationMessage;
use App\Repository\AdherentRepository;
use App\Repository\Event\EventRepository;
use App\Scope\ScopeEnum;
use App\Subscription\SubscriptionTypeEnum;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsMessageHandler]
class SendCreationNotificationCommandHandler
{
    public function __construct(
        private readonly EventRepository $eventRepository,
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

        if (!$event->isAnnounceEnabled() || $event->isInvitation()) {
            return;
        }

        $recipients = [];
        if ($agora = $event->agora) {
            $recipients = $this->adherentRepository->findInAgora($agora, TagEnum::ADHERENT, SubscriptionTypeEnum::EVENT_EMAIL);
        } elseif ($committee = $event->getCommittee()) {
            $recipients = $this->adherentRepository->findInCommittee($committee, TagEnum::ADHERENT, SubscriptionTypeEnum::EVENT_EMAIL);
        } elseif ($zones = $this->resolveNotificationZones($event)) {
            $recipients = $this->adherentRepository->findMembersAndAdherentsInZones($zones, SubscriptionTypeEnum::EVENT_EMAIL);
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

    /**
     * @return Zone[]
     */
    private function resolveNotificationZones(Event $event): array
    {
        if (ScopeEnum::MILITANT === $event->getAuthorScope()) {
            return $event->getCityOrBoroughZones();
        }

        return $event->getZones()->toArray();
    }
}
