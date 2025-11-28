<?php

declare(strict_types=1);

namespace App\Mailchimp\Synchronisation\Handler;

use App\Entity\NationalEvent\EventInscription;
use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Command\NationalEventInscriptionChangeCommand;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class NationalEventInscriptionChangeCommandHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        public readonly Manager $manager,
        public readonly EventInscriptionRepository $repository,
        public readonly ObjectManager $entityManager,
    ) {
        $this->logger = new NullLogger();
    }

    public function __invoke(NationalEventInscriptionChangeCommand $message): void
    {
        /** @var EventInscription $eventInscription */
        if (!$eventInscription = $this->repository->findOneByUuid($uuid = $message->uuid->toString())) {
            $this->logger->warning(\sprintf('EventInscription with UUID "%s" not found, message skipped', $uuid));

            return;
        }

        $this->entityManager->refresh($eventInscription);

        if (!$eventInscription->event->mailchimpSync) {
            return;
        }

        if ($adherent = $eventInscription->adherent) {
            $this->entityManager->refresh($adherent);
        }

        if (!$eventInscription->addressEmail) {
            return;
        }

        $this->manager->editNationalEventInscriptionMember($eventInscription, $message);

        $this->entityManager->clear();
    }
}
