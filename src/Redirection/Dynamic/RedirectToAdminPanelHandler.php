<?php

namespace App\Redirection\Dynamic;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * Handles dynamic redirections editable in the administration panel.
 */
class RedirectToAdminPanelHandler implements RedirectToInterface
{
    private $redirectionManager;

    public function __construct(RedirectionManager $redirectionManager)
    {
        $this->redirectionManager = $redirectionManager;
    }

    public function handle(GetResponseForExceptionEvent $event, string $requestUri, string $redirectCode): bool
    {
        if ($redirection = $this->redirectionManager->getRedirection($requestUri)) {
            $event->setResponse(new RedirectResponse($redirection->getTo(), $redirection->getType()));

            return true;
        }

        return false;
    }
}
