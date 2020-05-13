<?php

namespace App\IdeasWorkshop\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\IdeasWorkshop\Thread;
use App\Entity\IdeasWorkshop\ThreadComment;
use App\IdeasWorkshop\Command\SendMailForApprovedThreadCommentCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

class ApproveThreadCommentListener implements EventSubscriberInterface
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function sendApprovalMail(GetResponseForControllerResultEvent $event): void
    {
        $object = $event->getControllerResult();

        if (!$object instanceof Thread && !$object instanceof ThreadComment) {
            return;
        }

        if ('approve' === $event->getRequest()->attributes->get('_api_item_operation_name')) {
            $this->bus->dispatch(new SendMailForApprovedThreadCommentCommand($object));
        }
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::VIEW => ['sendApprovalMail', EventPriorities::POST_WRITE]];
    }
}
