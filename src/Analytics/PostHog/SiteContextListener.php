<?php

declare(strict_types=1);

namespace App\Analytics\PostHog;

use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Rôle : EventListener kernel.request priority=250.
 * Initialise SiteContext au boot de chaque requête main.
 * Return early si hostname hors périmètre PostHog (SiteDetector fail-open).
 */
final class SiteContextListener
{
    public function __construct(
        private readonly SiteDetector $detector,
        private readonly SiteContext $context,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }
        $site = $this->detector->detectFromRequest($event->getRequest());
        if (null === $site) {
            return; // Hors périmètre PostHog (admin/api/webhooks) — SiteContext reste non-init
        }
        $this->context->setSite($site);
    }
}
