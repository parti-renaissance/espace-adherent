<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\EventListener;

use App\Entity\Adherent;
use App\Image\Event\ImageUploadedEvent;
use App\JeMengage\Timeline\Command\IndexAllItemsForAuthorCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class IndexAllItemsForUpdatedAuthor implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $messageBus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [ImageUploadedEvent::class => 'onImageUploaded'];
    }

    public function onImageUploaded(ImageUploadedEvent $event): void
    {
        $author = $event->entity;

        if (!$author instanceof Adherent) {
            return;
        }

        $this->messageBus->dispatch(new IndexAllItemsForAuthorCommand($author->getUuid()));
    }
}
