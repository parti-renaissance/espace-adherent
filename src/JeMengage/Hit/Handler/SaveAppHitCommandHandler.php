<?php

namespace App\JeMengage\Hit\Handler;

use App\Entity\Adherent;
use App\Entity\AppHit;
use App\Entity\AppSession;
use App\JeMengage\Hit\Command\SaveAppHitCommand;
use App\JeMengage\Hit\Event\NewHitSavedEvent;
use App\JeMengage\Hit\EventTypeEnum;
use App\JeMengage\Hit\TargetTypeEnum;
use App\Repository\AdherentRepository;
use App\Repository\Event\EventRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsMessageHandler]
class SaveAppHitCommandHandler
{
    public function __construct(
        private readonly DenormalizerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly AdherentRepository $adherentRepository,
        private readonly EventRepository $eventRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(SaveAppHitCommand $command): void
    {
        /** @var AppHit $hit */
        $hit = $this->serializer->denormalize($command->data, AppHit::class, null, ['groups' => ['hit:write']]);
        $hit->raw = $command->data;

        $hit->adherent = $this->entityManager->getReference(Adherent::class, $command->userId);
        $hit->appSession = $command->sessionId ? $this->entityManager->getReference(AppSession::class, $command->sessionId) : null;

        if ($hit->referrerCode) {
            $hit->referrer = $this->adherentRepository->findByPublicId($hit->referrerCode, true);
        }

        if (EventTypeEnum::Click === $hit->eventType && empty($hit->source)) {
            $hit->source = 'app';
        }

        if (
            TargetTypeEnum::Event === $hit->objectType
            && $hit->objectId
            && !Uuid::isValid($hit->objectId)
            && $event = $this->eventRepository->findOneBySlug($hit->objectId)
        ) {
            $hit->objectId = $event->getUuidAsString();
        }

        $hit->updateFingerprintHash();

        $this->entityManager->persist($hit);

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
        }

        $this->eventDispatcher->dispatch(new NewHitSavedEvent($hit));
    }
}
