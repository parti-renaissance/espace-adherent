<?php

namespace App\NationalEvent\Listener;

use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\NationalEventInscriptionConfirmationMessage;
use App\NationalEvent\Event\NewNationalEventInscriptionEvent;
use App\Repository\Geo\ZoneRepository;
use App\ValueObject\Genders;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SendInscriptionConfirmationListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $secret,
        private readonly TranslatorInterface $translator,
        private readonly ZoneRepository $zoneRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [NewNationalEventInscriptionEvent::class => 'sendConfirmationEmail'];
    }

    public function sendConfirmationEmail(NewNationalEventInscriptionEvent $event): void
    {
        $eventInscription = $event->eventInscription;

        $departmentCode = substr($eventInscription->postalCode, 0, 2);
        $departments = $this->zoneRepository->findAllDepartmentsIndexByCode([$departmentCode]);
        $zone = $departments[$departmentCode] ?? null;

        $this->transactionalMailer->sendMessage(NationalEventInscriptionConfirmationMessage::create(
            $eventInscription,
            $this->urlGenerator->generate('app_national_event_edit_inscription', ['uuid' => $uuid = $event->eventInscription->getUuid()->toString(), 'token' => hash_hmac('sha256', $uuid, $this->secret)], UrlGeneratorInterface::ABSOLUTE_URL),
            civility: $eventInscription->gender ? $this->translator->trans(array_search($eventInscription->gender, Genders::CIVILITY_CHOICES, true)) : null,
            region: $zone['region_name'] ?? null,
            department: $zone['name'] ?? null
        ));
    }
}
