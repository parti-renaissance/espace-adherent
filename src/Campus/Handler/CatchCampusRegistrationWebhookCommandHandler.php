<?php

namespace App\Campus\Handler;

use App\Campus\Command\CatchCampusRegistrationWebhookCommand;
use App\Campus\RegistrationStatusEnum;
use App\Entity\Campus\Registration;
use App\Repository\AdherentRepository;
use App\Repository\Campus\RegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CatchCampusRegistrationWebhookCommandHandler implements MessageHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private readonly RegistrationRepository $registrationRepository,
        private AdherentRepository $adherentRepository
    ) {
    }

    public function __invoke(CatchCampusRegistrationWebhookCommand $command): void
    {
        $payload = json_decode($command->getPayload(), true);

        if (isset($payload['status']) && \in_array($payload['status'], RegistrationStatusEnum::toArray(), true)) {
            if (!$registration = $this->registrationRepository->findOneBy(['eventMakerId' => $payload['_id']])) {
                $registration = new Registration();
            }

            if ($registration->eventMakerId !== $payload['_id']) {
                $registration->eventMakerId = $payload['_id'];
            }

            if ($registration->campusEventId !== $payload['event_id']) {
                $registration->campusEventId = $payload['event_id'];
            }

            if ($registration->eventMakerOrderUid !== $payload['order_uid']) {
                $registration->eventMakerOrderUid = $payload['order_uid'];
            }

            if ($registration->status !== $payload['status']) {
                $registration->status = RegistrationStatusEnum::fromStatus($payload['status']);
            }

            $registeredAt = isset($payload['registered_at']) ? new \DateTime($payload['registered_at']) : null;
            if ($registration->registeredAt !== $registeredAt) {
                $registration->registeredAt = $registeredAt;
            }

            if ($registration->adherent?->getUuid()->toString() !== $payload['company_name']) {
                if (!$adherent = $this->adherentRepository->findOneByUuid($payload['company_name'])) {
                    return;
                }

                $registration->adherent = $adherent;
            }

            if (!$registration->getId()) {
                $this->entityManager->persist($registration);
            }

            $this->entityManager->flush();
        }
    }
}
