<?php

declare(strict_types=1);

namespace App\Filesystem;

use App\Entity\UploadableFile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;

class UploadableFileListener implements EventSubscriberInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [Events::POST_REMOVE => 'onPostRemove'];
    }

    public function onPostRemove(Event $event): void
    {
        if (!$event->getObject() instanceof UploadableFile) {
            return;
        }

        $this->entityManager->remove($event->getObject());
        $this->entityManager->flush();
    }
}
