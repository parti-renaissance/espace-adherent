<?php

namespace App\Phoning\Api\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Phoning\CampaignHistory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PostPhoningCampaignHistoryEditListener implements EventSubscriberInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::VIEW => ['dispatchPreCreateEvent', EventPriorities::POST_WRITE]];
    }

    public function dispatchPreCreateEvent(ViewEvent $event): void
    {
        $campaignHistory = $event->getControllerResult();

        if (
            !$event->getRequest()->isMethod(Request::METHOD_PUT)
            || !$campaignHistory instanceof CampaignHistory
        ) {
            return;
        }

        if (null === $campaignHistory->getFinishAt()
            && $campaignHistory->isInAfterCallStatus()) {
            $campaignHistory->setFinishAt(new \DateTime());
        }

        $this->entityManager->flush();
    }
}
