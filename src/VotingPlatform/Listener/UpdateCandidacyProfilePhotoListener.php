<?php

namespace App\VotingPlatform\Listener;

use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Image\ImageManagerInterface;
use App\VotingPlatform\Event\BaseCandidacyEvent;
use App\VotingPlatform\Events;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateCandidacyProfilePhotoListener implements EventSubscriberInterface
{
    private $imageManager;
    private $entityManager;

    public function __construct(ImageManagerInterface $imageManager, EntityManagerInterface $entityManager)
    {
        $this->imageManager = $imageManager;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::CANDIDACY_CREATED => 'onCandidacyCreated',
            Events::CANDIDACY_UPDATED => 'onCandidacyUpdated',
            Events::CANDIDACY_REMOVED => 'onCandidacyRemoved',
        ];
    }

    public function onCandidacyCreated(BaseCandidacyEvent $event): void
    {
        $this->updatePhoto($event->getCandidacy());
    }

    public function onCandidacyUpdated(BaseCandidacyEvent $event): void
    {
        $candidacy = $event->getCandidacy();

        if ($candidacy->isRemoveImage()) {
            if ($candidacy->hasImageName()) {
                $this->imageManager->removeImage($candidacy);
                $this->entityManager->flush();
            }
        } else {
            $this->updatePhoto($candidacy);
        }
    }

    public function onCandidacyRemoved(BaseCandidacyEvent $event): void
    {
        $candidacy = $event->getCandidacy();

        if ($candidacy->hasImageName()) {
            $this->imageManager->removeImage($event->getCandidacy());
        }
    }

    private function updatePhoto(CandidacyInterface $candidacy): void
    {
        if ($candidacy->getImage()) {
            $this->imageManager->saveImage($candidacy);
            $this->entityManager->flush();
        }
    }
}
