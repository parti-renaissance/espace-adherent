<?php

declare(strict_types=1);

namespace App\Redirection\Dynamic;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

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

    public function handle(ExceptionEvent $event, string $requestUri, int $redirectCode): bool
    {
        if ($redirection = $this->redirectionManager->getRedirection($requestUri)) {
            $event->setResponse(new RedirectResponse($redirection->getTo(), $redirection->getType()));

            return true;
        }

        return false;
    }
}
