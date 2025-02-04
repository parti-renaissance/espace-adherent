<?php

namespace App\Controller\Api\Event;

use App\Entity\Event\Event;
use App\Event\EventCanceledHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('REQUEST_SCOPE_GRANTED', 'events') and is_granted('CAN_MANAGE_EVENT', subject)"), subject: 'event')]
class CancelEventController extends AbstractController
{
    public function __invoke(EventCanceledHandler $handler, Request $request, Event $event): Response
    {
        if (Event::STATUS_CANCELLED === $event->getStatus()) {
            throw new BadRequestHttpException('this event is already cancelled');
        }

        $handler->handle($event);

        return $this->json('OK');
    }
}
