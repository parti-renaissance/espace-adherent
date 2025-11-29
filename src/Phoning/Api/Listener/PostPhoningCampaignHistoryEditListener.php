<?php

declare(strict_types=1);

namespace App\Phoning\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Phoning\CampaignHistory;
use App\Phoning\Command\SendAdherentActionSummaryCommand;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

class PostPhoningCampaignHistoryEditListener implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private MessageBusInterface $bus;

    public function __construct(EntityManagerInterface $entityManager, MessageBusInterface $bus)
    {
        $this->entityManager = $entityManager;
        $this->bus = $bus;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['dispatchPostWrite', EventPriorities::POST_WRITE]];
    }

    public function dispatchPostWrite(ViewEvent $event): void
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

        if (
            $campaignHistory->getAdherent()
            && ($campaignHistory->isPostalCodeChecked() + $campaignHistory->getNeedEmailRenewal() + $campaignHistory->getNeedSmsRenewal()) > 0
        ) {
            $this->bus->dispatch(new SendAdherentActionSummaryCommand($campaignHistory));
        }
    }
}
