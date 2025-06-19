<?php

namespace App\NationalEvent\Handler;

use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\InscriptionReminder;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\NationalEventInscriptionPaymentReminderMessage;
use App\NationalEvent\Command\SendPaymentReminderCommand;
use App\NationalEvent\InscriptionReminderTypeEnum;
use App\NationalEvent\InscriptionStatusEnum;
use App\Repository\NationalEvent\EventInscriptionRepository;
use App\Repository\NationalEvent\InscriptionReminderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsMessageHandler]
class SendPaymentReminderCommandHandler
{
    public function __construct(
        private readonly EventInscriptionRepository $eventInscriptionRepository,
        private readonly InscriptionReminderRepository $inscriptionReminderRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly MailerService $transactionalMailer,
        private readonly string $userVoxHost,
    ) {
    }

    public function __invoke(SendPaymentReminderCommand $command): void
    {
        /** @var EventInscription|null $eventInscription */
        if (!$eventInscription = $this->eventInscriptionRepository->findOneByUuid($command->getUuid()->toString())) {
            return;
        }

        if (InscriptionStatusEnum::WAITING_PAYMENT !== $eventInscription->status) {
            return;
        }

        if (!$reminderType = $this->determineReminderType($eventInscription)) {
            return;
        }

        if ($this->inscriptionReminderRepository->count(['inscription' => $eventInscription, 'type' => $reminderType])) {
            return;
        }

        if (empty($eventInscription->transport) || empty($eventInscription->transportCosts)) {
            throw new \LogicException(\sprintf('Cannot send payment reminder for an inscription [%s] without transport costs.', $eventInscription->getId()));
        }

        $this->transactionalMailer->sendMessage(NationalEventInscriptionPaymentReminderMessage::create(
            $eventInscription,
            $this->urlGenerator->generate('app_national_event_payment', [
                'slug' => $eventInscription->event->getSlug(),
                'uuid' => $eventInscription->getUuid()->toString(),
                'app_domain' => $this->userVoxHost,
            ])
        ));
        $this->entityManager->persist(new InscriptionReminder($eventInscription, $reminderType));

        $this->entityManager->flush();
    }

    public function determineReminderType(EventInscription $eventInscription): ?InscriptionReminderTypeEnum
    {
        $minutesSinceInscription = (new \DateTimeImmutable())->diff($eventInscription->getCreatedAt())->days * 24 * 60
            + (new \DateTimeImmutable())->diff($eventInscription->getCreatedAt())->h * 60
            + (new \DateTimeImmutable())->diff($eventInscription->getCreatedAt())->i;

        return match (true) {
            $minutesSinceInscription < 10 => null,
            $minutesSinceInscription < 60 => InscriptionReminderTypeEnum::PAYMENT_10MIN,
            $minutesSinceInscription < 360 => InscriptionReminderTypeEnum::PAYMENT_1H,
            $minutesSinceInscription < 720 => InscriptionReminderTypeEnum::PAYMENT_6H,
            $minutesSinceInscription < 1200 => InscriptionReminderTypeEnum::PAYMENT_12H,
            default => InscriptionReminderTypeEnum::PAYMENT_20H,
        };
    }
}
