<?php

declare(strict_types=1);

namespace App\Adhesion;

use App\Entity\Renaissance\Adhesion\AdherentRequest;
use App\Entity\Renaissance\Adhesion\AdherentRequestReminder;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\AdherentRequest\AdherentRequestReminderAfterOneHourMessage;
use App\Mailer\Message\Renaissance\AdherentRequest\AdherentRequestReminderAfterThreeWeeksMessage;
use App\Mailer\Message\Renaissance\AdherentRequest\AdherentRequestReminderNextSaturdayMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AdherentRequestNotifier
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly MailerService $transactionalMailer,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function sendReminderMessage(AdherentRequest $adherentRequest, AdherentRequestReminderTypeEnum $reminderType): void
    {
        $messageClass = match ($reminderType) {
            AdherentRequestReminderTypeEnum::AFTER_ONE_HOUR => AdherentRequestReminderAfterOneHourMessage::class,
            AdherentRequestReminderTypeEnum::NEXT_SATURDAY => AdherentRequestReminderNextSaturdayMessage::class,
            AdherentRequestReminderTypeEnum::AFTER_THREE_WEEKS => AdherentRequestReminderAfterThreeWeeksMessage::class,
        };

        $adhesionLink = $this->urlGenerator->generate('app_adhesion_index', [
            'email' => $adherentRequest->email,
            'utm_source' => 'email',
            'utm_campaign' => $messageClass,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->transactionalMailer->sendMessage($messageClass::create($adherentRequest, $adhesionLink));

        $this->entityManager->persist(AdherentRequestReminder::createForAdherentRequest($adherentRequest, $reminderType));
        $this->entityManager->flush();
    }
}
