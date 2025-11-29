<?php

declare(strict_types=1);

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Phoning\Campaign;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PhoningCampaignParticipantsCountUpdateListener implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private AdherentRepository $adherentRepository;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        AdherentRepository $adherentRepository,
        LoggerInterface $logger,
    ) {
        $this->entityManager = $entityManager;
        $this->adherentRepository = $adherentRepository;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['onCampaignChange', EventPriorities::POST_WRITE]];
    }

    public function onCampaignChange(ViewEvent $viewEvent): void
    {
        $campaign = $viewEvent->getControllerResult();
        $request = $viewEvent->getRequest();
        if (
            !$campaign instanceof Campaign
            || !\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT])
        ) {
            return;
        }

        if ($campaign->getAudience()) {
            try {
                $campaign->setParticipantsCount((int) $this->adherentRepository->findForPhoningCampaign($campaign)->getTotalItems());
                $this->entityManager->flush();
            } catch (\Exception $e) {
                $this->logger->error(
                    \sprintf('Error while updating campaign "%s". Message: "%s".',
                        $campaign->getId(),
                        $e->getMessage())
                );
            }
        }
    }
}
