<?php

namespace AppBundle\Redirection;

use AppBundle\Repository\EventRepository;
use AppBundle\Repository\RedirectionRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

/**
 * Handle dynamic redirections editable in the administration panel.
 */
class DynamicRedirectionsSubscriber implements EventSubscriberInterface
{
    const REDIRECTIONS = [
        '/evenements/' => '/evenements',
        '/comites/' => '/comites',
    ];

    private $redirectRepository;
    private $urlMatcher;
    private $eventRepository;

    public function __construct(RedirectionRepository $redirectionRepository, EventRepository $eventRepository, UrlMatcherInterface $urlMatcher)
    {
        $this->redirectRepository = $redirectionRepository;
        $this->urlMatcher = $urlMatcher;
        $this->eventRepository = $eventRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'kernel.exception' => 'onKernelException',
        ];
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (!$event->getException() instanceof NotFoundHttpException) {
            return;
        }

        $requestUri = rtrim($event->getRequest()->getRequestUri(), '/');

        if ($redirection = $this->redirectRepository->findOneByOriginUri($requestUri)) {
            $event->setResponse(new RedirectResponse($redirection->getTo(), $redirection->getType()));

            return;
        }

        $redirectCode = Response::HTTP_MOVED_PERMANENTLY;
        foreach (self::REDIRECTIONS as $patternToMatch => $urlToRedirect) {
            if (0 !== strpos($requestUri, $patternToMatch)) {
                continue;
            }

            if ('/evenements/' === $patternToMatch
                && ($routeParams = $this->urlMatcher->match($requestUri))
                && isset($routeParams['uuid'])
                && ($eventEntity = $this->eventRepository->findOneByUuid($routeParams['uuid']))
                && !$eventEntity->isPublished()) {
                $redirectCode = Response::HTTP_FOUND;
            }

            $event->setResponse(new RedirectResponse($urlToRedirect, $redirectCode));

            return;
        }
    }
}
