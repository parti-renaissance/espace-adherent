<?php

namespace AppBundle\Redirection\Dynamic;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handle dynamic redirections.
 */
class RedirectionsSubscriber implements EventSubscriberInterface
{
    /**
     * @var RedirectToInterface[]
     */
    private $handlers = [];

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

        $requestUri = $event->getRequest()->getRequestUri();
        /* @var RedirectToInterface $handler */
        foreach ($this->handlers as $handler) {
            if ($handler->handle($event, $requestUri, Response::HTTP_MOVED_PERMANENTLY)) {
                return;
            }
        }

        $pathInfo = $event->getRequest()->getPathInfo();
        if ('/' !== substr($pathInfo, -1)) {
            return;
        }

        $handled = false;

        // Do the same handling for the same URL but without trailing slash
        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

        if (empty($url)) {
            return;
        }

        foreach ($this->handlers as $handler) {
            if ($handled = $handler->handle($event, $url, Response::HTTP_MOVED_PERMANENTLY)) {
                break;
            }
        }

        if (!$handled) {
            $event->setResponse(new RedirectResponse(rtrim($url, '/'), Response::HTTP_MOVED_PERMANENTLY));
        }
    }

    /**
     * Adds a RedirectTo handler.
     */
    public function addHandler(RedirectToInterface $handler): void
    {
        if (!\in_array($handler, $this->handlers)) {
            $this->handlers[] = $handler;
        }
    }
}
