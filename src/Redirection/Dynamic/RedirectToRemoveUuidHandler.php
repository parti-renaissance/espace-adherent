<?php

namespace AppBundle\Redirection\Dynamic;

use AppBundle\Repository\EventRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Routing\RouterInterface;

class RedirectToRemoveUuidHandler extends AbstractRedirectTo implements RedirectToInterface
{
    private $provider;
    private $eventRepository;
    private $router;
    private $patternUuid;

    public function __construct(
        RedirectionsProvider $provider,
        RouterInterface $router,
        EventRepository $eventRepository,
        string $patternUuid
    ) {
        $this->provider = $provider;
        $this->router = $router;
        $this->eventRepository = $eventRepository;
        $this->patternUuid = $patternUuid;
    }

    public function handle(GetResponseForExceptionEvent $event, string $requestUri, string $redirectCode): bool
    {
        foreach ($this->provider->get(RedirectionsProvider::TO_REMOVE_UUID) as $pattern => $path) {
            if (!$this->hasPattern($pattern, $requestUri)) {
                continue;
            }

            // Removes the uuid for all old URLs with uuid/slug
            if (null !== $uuid = $this->getUuid($requestUri)) {
                $path = str_replace($uuid, '', $requestUri);
                $event->setResponse(new RedirectResponse($path, $redirectCode));

                return true;
            }

            // Handles the redirect status code
            try {
                $routeParams = $this->router->match($requestUri);
            } catch (\Throwable $e) {
                $routeParams = false;
            }

            if ('/evenements/' === $pattern && $routeParams && isset($routeParams['uuid'])
                && ($eventEntity = $this->eventRepository->findOneByUuid($routeParams['uuid']))
                && !$eventEntity->isPublished()) {
                $redirectCode = Response::HTTP_FOUND;
            }

            $event->setResponse(new RedirectResponse($path, $redirectCode));

            return true;
        }

        return false;
    }

    protected function getUuid(string $requestUri): ?string
    {
        preg_match(sprintf("/\/%s/", $this->patternUuid), $requestUri, $matches);

        return $matches[0] ?? null;
    }
}
