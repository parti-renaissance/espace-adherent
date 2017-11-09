<?php

namespace AppBundle\Redirection\Dynamic;

use AppBundle\Repository\RedirectionRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * Handles dynamic redirections editable in the administration panel.
 */
class RedirectToAdminPanelHandler implements RedirectToInterface
{
    private $provider;
    private $redirectionRepository;

    public function __construct(RedirectionsProvider $provider, RedirectionRepository $redirectionRepository)
    {
        $this->provider = $provider;
        $this->redirectionRepository = $redirectionRepository;
    }

    public function handle(GetResponseForExceptionEvent $event, string $requestUri, string $redirectCode): bool
    {
        if ($redirection = $this->redirectionRepository->findOneByOriginUri($requestUri)) {
            $event->setResponse(new RedirectResponse($redirection->getTo(), $redirection->getType()));

            return true;
        }

        return false;
    }
}
