<?php

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\MyTeam\Member;
use App\History\UserActionHistoryHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class MyTeamMemberListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly UserActionHistoryHandler $userActionHistoryHandler,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onMemberChange', EventPriorities::POST_WRITE],
        ];
    }

    public function onMemberChange(ViewEvent $event): void
    {
        $member = $event->getControllerResult();

        if (!$event->isMainRequest() || !$member instanceof Member) {
            return;
        }

        $method = $event->getRequest()->getMethod();

        match ($method) {
            Request::METHOD_POST => $this->userActionHistoryHandler->createTeamMemberAdd($member),
            Request::METHOD_PUT => $this->userActionHistoryHandler->createTeamMemberEdit($member),
            Request::METHOD_DELETE => $this->userActionHistoryHandler->createTeamMemberRemove($member),
            default => null,
        };
    }
}
