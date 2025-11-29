<?php

declare(strict_types=1);

namespace App\NationalEvent;

use App\Entity\NationalEvent\EventInscription;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\NationalEventInscriptionConfirmationMessage;
use App\Mailer\Message\Renaissance\NationalEventInscriptionDuplicateMessage;
use App\Mailer\Message\Renaissance\NationalEventInscriptionPaymentReminderMessage;
use App\Mailer\Message\Renaissance\NationalEventTicketMessage;
use App\Repository\OAuth\ClientRepository;
use App\ValueObject\Genders;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Notifier
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface $translator,
        private readonly ClientRepository $clientRepository,
    ) {
    }

    public function sendDuplicateNotification(EventInscription $originalInscription): void
    {
        $this->transactionalMailer->sendMessage(NationalEventInscriptionDuplicateMessage::create(
            $originalInscription,
            $this->urlGenerator->generate('app_national_event_my_inscription', [
                'slug' => $originalInscription->event->getSlug(),
                'uuid' => $originalInscription->getUuid()->toString(),
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }

    public function sendPaymentReminder(EventInscription $eventInscription): void
    {
        $this->transactionalMailer->sendMessage(NationalEventInscriptionPaymentReminderMessage::create(
            $eventInscription,
            $this->urlGenerator->generate('app_national_event_new_payment', [
                'slug' => $eventInscription->event->getSlug(),
                'uuid' => $eventInscription->getUuid()->toString(),
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }

    public function sendTicket(EventInscription $eventInscription): void
    {
        $message = NationalEventTicketMessage::create(
            $eventInscription,
            !empty($eventInscription->adherent?->findAppSessions($this->clientRepository->getVoxClient(), true))
        );

        $this->transactionalMailer->sendMessage($message, false);
    }

    public function sendInscriptionConfirmation(EventInscription $eventInscription, array $zone): void
    {
        $this->transactionalMailer->sendMessage(NationalEventInscriptionConfirmationMessage::create(
            $eventInscription,
            $this->urlGenerator->generate('app_national_event_my_inscription', ['uuid' => $eventInscription->getUuid()->toString(), 'slug' => $eventInscription->event->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
            $this->urlGenerator->generate('app_national_event_by_slug', ['slug' => $eventInscription->event->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
            $eventInscription->adherent ? $this->urlGenerator->generate('app_national_event_by_slug_with_referrer', ['slug' => $eventInscription->event->getSlug(), 'pid' => $eventInscription->adherent->getPublicId()], UrlGeneratorInterface::ABSOLUTE_URL) : null,
            civility: $eventInscription->gender ? $this->translator->trans(array_search($eventInscription->gender, Genders::CIVILITY_CHOICES, true)) : null,
            region: $zone['region_name'] ?? null,
            department: $zone['name'] ?? null
        ));
    }
}
