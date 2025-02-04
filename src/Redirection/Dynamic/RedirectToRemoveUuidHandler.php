<?php

namespace App\Redirection\Dynamic;

use App\Repository\Event\EventRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

class RedirectToRemoveUuidHandler extends AbstractRedirectTo implements RedirectToInterface
{
    private $provider;
    private $eventRepository;
    private $urlMatcher;
    private $patternUuid;

    public function __construct(
        RedirectionsProvider $provider,
        UrlMatcherInterface $urlMatcher,
        EventRepository $eventRepository,
        string $patternUuid,
    ) {
        $this->provider = $provider;
        $this->urlMatcher = $urlMatcher;
        $this->eventRepository = $eventRepository;
        $this->patternUuid = $patternUuid;
    }

    public function handle(ExceptionEvent $event, string $requestUri, string $redirectCode): bool
    {
        foreach ($this->provider->get(RedirectionsProvider::TO_REMOVE_UUID) as $pattern => $path) {
            if (!$this->hasPattern($pattern, $requestUri)) {
                continue;
            }

            // Removes the uuid for all old URLs with uuid/slug
            if (null !== $uuid = $this->getUuid($requestUri)) {
                if (!preg_match(\sprintf('#%s%s#', $pattern, $this->patternUuid), $requestUri)) {
                    continue;
                }

                $path = str_replace($uuid, '', $requestUri);
                $event->setResponse(new RedirectResponse($path, $redirectCode));

                return true;
            }

            // Handles the redirect status code
            try {
                $routeParams = $this->urlMatcher->match($requestUri);
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
        preg_match(\sprintf("/\/%s/", $this->patternUuid), $requestUri, $matches);

        return $matches[0] ?? null;
    }
}
