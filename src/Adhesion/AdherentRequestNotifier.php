<?php

namespace App\Adhesion;

use App\Entity\Renaissance\Adhesion\AdherentRequest;
use App\Entity\Renaissance\Adhesion\AdherentRequestReminder;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\AdherentRequest\AdherentRequestReminderAfterOneHourMessage;
use App\Mailer\Message\Renaissance\AdherentRequest\AdherentRequestReminderAfterThreeWeeksMessage;
use App\Mailer\Message\Renaissance\AdherentRequest\AdherentRequestReminderNextSaturdayMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AdherentRequestNotifier
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly MailerService $transactionalMailer,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function sendReminderMessage(AdherentRequest $adherentRequest, AdherentRequestReminderTypeEnum $reminderType): void
    {
        $adhesionLink = $this->urlGenerator->generate('app_adhesion_index', [
            'email' => $adherentRequest->email,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $message = match ($reminderType) {
            AdherentRequestReminderTypeEnum::AFTER_ONE_HOUR => AdherentRequestReminderAfterOneHourMessage::create($adherentRequest, $adhesionLink),
            AdherentRequestReminderTypeEnum::NEXT_SATURDAY => AdherentRequestReminderNextSaturdayMessage::create($adherentRequest, $adhesionLink),
            AdherentRequestReminderTypeEnum::AFTER_THREE_WEEKS => AdherentRequestReminderAfterThreeWeeksMessage::create($adherentRequest, $adhesionLink),
            default => null,
        };

        if (!$message) {
            $this->logger->log(\sprintf('Reminder type "%s" is not handled by "%s".', $reminderType->value, self::class));

            return;
        }

        $this->transactionalMailer->sendMessage($message);

        $this->entityManager->persist(AdherentRequestReminder::createForAdherentRequest($adherentRequest, $reminderType));
        $this->entityManager->flush();
    }
}
