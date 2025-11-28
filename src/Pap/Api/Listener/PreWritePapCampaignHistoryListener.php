<?php

declare(strict_types=1);

namespace App\Pap\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Adherent;
use App\Entity\Pap\CampaignHistory;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PreWritePapCampaignHistoryListener implements EventSubscriberInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['preWrite', EventPriorities::PRE_WRITE]];
    }

    public function preWrite(ViewEvent $event): void
    {
        $campaignHistory = $event->getControllerResult();

        if (!$campaignHistory instanceof CampaignHistory) {
            return;
        }

        if (\in_array($event->getRequest()->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT])
            && $campaignHistory->isFinishedStatus()
            && null === $campaignHistory->getFinishAt()
        ) {
            $campaignHistory->setFinishAt(new \DateTime());
        }

        if (null !== $campaignHistory->getQuestioner()) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof Adherent) {
            throw new \RuntimeException('User is not a connected adherent');
        }

        $campaignHistory->setQuestioner($this->security->getUser());
    }
}
