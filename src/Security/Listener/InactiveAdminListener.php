<?php

declare(strict_types=1);

namespace App\Security\Listener;

use App\Entity\Adherent;
use App\Entity\Administrator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class InactiveAdminListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Security $security,
        private readonly int $maxIdleTime,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$user = $this->security->getUser()) {
            return;
        }

        if ($this->maxIdleTime > 0 && ($user instanceof Administrator || ($user instanceof Adherent && $this->security->isGranted('IS_IMPERSONATOR')))) {
            $lapse = time() - $this->requestStack->getSession()->getMetadataBag()->getLastUsed();

            if ($lapse > $this->maxIdleTime) {
                $event->setResponse($this->security->logout(false));
            }
        }
    }
}
