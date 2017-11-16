<?php

namespace AppBundle\Redirection\Dynamic;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handle dynamic redirections.
 */
class RedirectionsSubscriber implements EventSubscriberInterface
{
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

        $requestUri = rtrim($event->getRequest()->getRequestUri(), '/');

        /* @var RedirectToInterface $handler */
        foreach ($this->handlers as $handler) {
            if ($handler->handle($event, $requestUri, Response::HTTP_MOVED_PERMANENTLY)) {
                break;
            }
        }
    }

    /**
     * Adds a RedirectTo handler.
     *
     * @param RedirectToInterface $handler
     */
    public function addHandler(RedirectToInterface $handler): void
    {
        if (!in_array($handler, $this->handlers)) {
            $this->handlers[] = $handler;
        }
    }
}
