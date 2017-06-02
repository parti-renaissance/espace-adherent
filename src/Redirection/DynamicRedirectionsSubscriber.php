<?php

namespace AppBundle\Redirection;

use AppBundle\Entity\Redirection;
use AppBundle\Repository\RedirectionRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handle dynamic redirections editable in the administration panel.
 */
class DynamicRedirectionsSubscriber implements EventSubscriberInterface
{
    private $repository;

    public function __construct(RedirectionRepository $repository)
    {
        $this->repository = $repository;
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

        /** @var Redirection $redirection */
        $redirection = $this->repository->findOneBy(['from' => $requestUri]);

        if (!$redirection) {
            return;
        }

        $event->setResponse(new RedirectResponse($redirection->getTo(), $redirection->getType()));
    }
}
