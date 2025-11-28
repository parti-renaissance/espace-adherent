<?php

declare(strict_types=1);

namespace App\Redirection\Dynamic;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Handle dynamic redirections.
 */
class RedirectionsSubscriber implements EventSubscriberInterface
{
    private $handlers;

    /**
     * @param RedirectToInterface[]
     */
    public function __construct(iterable $handlers)
    {
        $this->handlers = $handlers;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$event->getThrowable() instanceof NotFoundHttpException) {
            return;
        }

        $path = $event->getRequest()->getPathInfo();

        /* @var RedirectToInterface $handler */
        foreach ($this->handlers as $handler) {
            if ($handler->handle($event, $path, Response::HTTP_MOVED_PERMANENTLY)) {
                return;
            }
        }

        if ('/' !== substr($path, -1)) {
            return;
        }

        $handled = false;

        // Do the same handling for the same URL but without trailing slash
        $path = rtrim($path, ' /');
        if (empty($path)) {
            return;
        }

        foreach ($this->handlers as $handler) {
            if ($handled = $handler->handle($event, $path, Response::HTTP_MOVED_PERMANENTLY)) {
                break;
            }
        }

        if (!$handled) {
            $event->setResponse(new RedirectResponse(
                str_replace($event->getRequest()->getPathInfo(), $path, $event->getRequest()->getRequestUri()),
                Response::HTTP_MOVED_PERMANENTLY
            ));
        }
    }
}
