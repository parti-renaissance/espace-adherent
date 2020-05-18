<?php

namespace App\VotingPlatform\Listener;

use App\Entity\CommitteeCandidacy;
use App\Image\ImageManager;
use App\VotingPlatform\Event\CommitteeCandidacyEvent;
use App\VotingPlatform\Event\UpdateCommitteeCandidacyEvent;
use App\VotingPlatform\Events;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateCandidacyProfilePhotoListener implements EventSubscriberInterface
{
    private $imageManager;
    private $entityManager;

    public function __construct(ImageManager $imageManager, EntityManagerInterface $entityManager)
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

    public function onCandidacyCreated(CommitteeCandidacyEvent $event): void
    {
        $this->updatePhoto($event->getCommitteeCandidacy());
    }

    public function onCandidacyUpdated(UpdateCommitteeCandidacyEvent $event): void
    {
        $candidacy = $event->getCommitteeCandidacy();

        if ($candidacy->isRemoveImage()) {
            if ($candidacy->hasImageName()) {
                $this->imageManager->removeImage($candidacy);
                $this->entityManager->flush();
            }
        } else {
            $this->updatePhoto($candidacy);
        }
    }

    public function onCandidacyRemoved(CommitteeCandidacyEvent $event): void
    {
        $candidacy = $event->getCommitteeCandidacy();

        if ($candidacy->hasImageName()) {
            $this->imageManager->removeImage($event->getCommitteeCandidacy());
        }
    }

    private function updatePhoto(CommitteeCandidacy $candidacy): void
    {
        if ($candidacy->getImage()) {
            $this->imageManager->saveImage($candidacy);
            $this->entityManager->flush();
        }
    }
}
