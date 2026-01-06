<?php

declare(strict_types=1);

namespace App\NationalEvent;

use App\Entity\NationalEvent\EventInscription;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\JEMNationalEventInscriptionConfirmationMessage;
use App\Mailer\Message\Renaissance\JEMNationalEventInscriptionDuplicateMessage;
use App\Mailer\Message\Renaissance\JEMNationalEventInscriptionPaymentReminderMessage;
use App\Mailer\Message\Renaissance\JEMNationalEventTicketMessage;
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
        $url = $this->urlGenerator->generate('app_national_event_my_inscription', [
            'slug' => $originalInscription->event->getSlug(),
            'uuid' => $originalInscription->getUuid()->toString(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        if ($originalInscription->event->isJEM()) {
            $message = JEMNationalEventInscriptionDuplicateMessage::create($originalInscription, $url);
        } else {
            $message = NationalEventInscriptionDuplicateMessage::create($originalInscription, $url);
        }

        $this->transactionalMailer->sendMessage($message);
    }

    public function sendPaymentReminder(EventInscription $eventInscription): void
    {
        $url = $this->urlGenerator->generate('app_national_event_new_payment', [
            'slug' => $eventInscription->event->getSlug(),
            'uuid' => $eventInscription->getUuid()->toString(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        if ($eventInscription->event->isJEM()) {
            $message = JEMNationalEventInscriptionPaymentReminderMessage::create($eventInscription, $url);
        } else {
            $message = NationalEventInscriptionPaymentReminderMessage::create($eventInscription, $url);
        }

        $this->transactionalMailer->sendMessage($message);
    }

    public function sendTicket(EventInscription $eventInscription): void
    {
        $useAppMobile = !empty($eventInscription->adherent?->findAppSessions($this->clientRepository->getVoxClient(), true));

        if ($eventInscription->event->isJEM()) {
            $message = JEMNationalEventTicketMessage::create($eventInscription, $useAppMobile);
        } else {
            $message = NationalEventTicketMessage::create($eventInscription, $useAppMobile);
        }

        $this->transactionalMailer->sendMessage($message, false);
    }

    public function sendInscriptionConfirmation(EventInscription $eventInscription, array $zone): void
    {
        $editUrl = $this->urlGenerator->generate('app_national_event_my_inscription', ['uuid' => $eventInscription->getUuid()->toString(), 'slug' => $eventInscription->event->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
        $eventUrl = $this->urlGenerator->generate('app_national_event_by_slug', ['slug' => $eventInscription->event->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
        $shareUrl = $eventInscription->adherent ? $this->urlGenerator->generate('app_national_event_by_slug_with_referrer', ['slug' => $eventInscription->event->getSlug(), 'pid' => $eventInscription->adherent->getPublicId()], UrlGeneratorInterface::ABSOLUTE_URL) : null;
        $civility = $eventInscription->gender ? $this->translator->trans(array_search($eventInscription->gender, Genders::CIVILITY_CHOICES, true)) : null;
        $region = $zone['region_name'] ?? null;
        $department = $zone['name'] ?? null;

        if ($eventInscription->event->isJEM()) {
            $message = JEMNationalEventInscriptionConfirmationMessage::create($eventInscription, $editUrl, $eventUrl, $shareUrl, $civility, $region, $department);
        } else {
            $message = NationalEventInscriptionConfirmationMessage::create($eventInscription, $editUrl, $eventUrl, $shareUrl, $civility, $region, $department);
        }

        $this->transactionalMailer->sendMessage($message);
    }
}
