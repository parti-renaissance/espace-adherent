<?php

namespace App\Controller\Api\Event;

use App\Entity\Event\BaseEvent;
use App\Event\EventCanceledHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[Security("is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('CAN_MANAGE_EVENT', event)")]
class CancelEventController extends AbstractController
{
    public function __invoke(EventCanceledHandler $handler, Request $request, BaseEvent $event): Response
    {
        if (BaseEvent::STATUS_CANCELLED === $event->getStatus()) {
            throw new BadRequestHttpException('this event is already cancelled');
        }

        $handler->handle($event);

        return $this->json('OK');
    }
}
