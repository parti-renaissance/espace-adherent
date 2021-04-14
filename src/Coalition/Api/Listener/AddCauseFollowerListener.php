<?php

namespace App\Coalition\Api\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Coalition\CauseFollowerChangeEvent;
use App\Entity\Coalition\Cause;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AddCauseFollowerListener implements EventSubscriberInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            CauseFollowerChangeEvent::class => 'updateFollowersCount',
            KernelEvents::VIEW => ['updateFollowersCountForApip', EventPriorities::POST_WRITE],
        ];
    }

    public function updateFollowersCount(CauseFollowerChangeEvent $event): void
    {
        $event->getCause()->refreshFollowersCount();

        $this->entityManager->flush();
    }

    public function updateFollowersCountForApip(ViewEvent $event): void
    {
        $data = $event->getRequest()->attributes->get('data');
        $operationName = $event->getRequest()->attributes->get('_api_item_operation_name');

        if ('follow' !== $operationName || !$data instanceof Cause) {
            return;
        }

        $data->refreshFollowersCount();

        $this->entityManager->flush();
    }
}
