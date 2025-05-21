<?php

namespace App\Adherent\Referral\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Adherent\Referral\Notifier;
use App\Entity\Referral;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PostReferralCreationListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Notifier $notifier,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['onSubscribe', EventPriorities::POST_WRITE]];
    }

    public function onSubscribe(ViewEvent $viewEvent): void
    {
        $request = $viewEvent->getRequest();

        if ('_api_/v3/referrals_post' !== $request->attributes->get('_route')) {
            return;
        }

        $referral = $request->attributes->get('data');
        if (!$referral instanceof Referral) {
            return;
        }

        if (
            !$referral->referred
            && ($adherent = $this->adherentRepository->findOneByEmail($referral->emailAddress))
            && $adherent->isRenaissanceSympathizer()
            && ($adherent->isPending() || $adherent->isEnabled())
        ) {
            $referral->referred = $adherent;
            $referral->forSympathizer = true;

            $this->entityManager->flush();
        }

        if ($referral->isAdhesion()) {
            $this->notifier->sendAdhesionMessage($referral);
        }
    }
}
