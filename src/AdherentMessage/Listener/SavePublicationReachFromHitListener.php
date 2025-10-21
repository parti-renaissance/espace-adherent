<?php

namespace App\AdherentMessage\Listener;

use App\Entity\AdherentMessage\AdherentMessageReach;
use App\JeMengage\Hit\Event\NewHitSavedEvent;
use App\JeMengage\Hit\TargetTypeEnum;
use App\Repository\AdherentMessageRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SavePublicationReachFromHitListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdherentMessageRepository $adherentMessageRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [NewHitSavedEvent::class => 'onNewHitSavedEvent'];
    }

    public function onNewHitSavedEvent(NewHitSavedEvent $event): void
    {
        $hit = $event->hit;

        if (TargetTypeEnum::Publication !== $hit->objectType || !$hit->isImpression()) {
            return;
        }

        if (!$adherentMessage = $this->adherentMessageRepository->findOneByUuid($hit->objectId)) {
            return;
        }

        $this->entityManager->persist(AdherentMessageReach::createApp(
            $adherentMessage,
            $hit->adherent,
            $hit->getCreatedAt(),
        ));

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
        }
    }
}
