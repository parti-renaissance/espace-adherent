<?php

declare(strict_types=1);

namespace App\JeMengage\Hit\Handler;

use App\Entity\Adherent;
use App\Entity\AppHit;
use App\Entity\AppSession;
use App\JeMengage\Hit\Command\SaveAppHitCommand;
use App\JeMengage\Hit\Event\NewHitSavedEvent;
use App\JeMengage\Hit\EventTypeEnum;
use App\JeMengage\Hit\SourceGroupEnum;
use App\JeMengage\Hit\TargetTypeEnum;
use App\Repository\AdherentRepository;
use App\Repository\Event\EventRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Uid\Uuid;
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
        private readonly LoggerInterface $logger,
        private readonly RateLimiterFactory $emptyHitSourceLogLimiter,
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

        if (str_contains((string) $hit->source, 'notification')) {
            $hit->sourceGroup = SourceGroupEnum::Notification;
        }

        if (
            TargetTypeEnum::Event->value === $hit->objectType
            && $hit->objectId
            && !Uuid::isValid($hit->objectId)
            && $event = $this->eventRepository->findOneBySlug($hit->objectId)
        ) {
            $hit->objectId = $event->getUuidAsString();
        }

        if (
            empty($hit->source)
            && EventTypeEnum::ActivitySession !== $hit->eventType
            && $this->emptyHitSourceLogLimiter->create('global')->consume()->isAccepted()
        ) {
            $this->logger->error('Received hit with empty source', [
                'raw' => $hit->raw,
                'event_type' => $hit->eventType?->value,
                'object_type' => $hit->objectType,
            ]);
        }

        $hit->updateFingerprintHash();

        $this->entityManager->persist($hit);

        try {
            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(new NewHitSavedEvent($hit));
        } catch (UniqueConstraintViolationException $e) {
        }
    }
}
