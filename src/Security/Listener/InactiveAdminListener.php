<?php

namespace App\Security\Listener;

use App\Entity\Adherent;
use App\Entity\Administrator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class InactiveAdminListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Security $security,
        private readonly UrlGeneratorInterface $urlGenerator,
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
                $this->security->logout(false);

                $event->setResponse(new RedirectResponse($this->urlGenerator->generate('sonata_admin_dashboard')));
            }
        }
    }
}
