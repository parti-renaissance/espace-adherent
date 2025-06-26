<?php

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\History\UserActionEvents;
use App\History\UserCommitteeActionEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CommitteeHistoryListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly Security $security,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['onCommitteePreWrite', EventPriorities::PRE_WRITE],
                ['onCommitteeWrite', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function onCommitteePreWrite(ViewEvent $viewEvent): void
    {
        $committee = $viewEvent->getControllerResult();
        $request = $viewEvent->getRequest();
        $user = $this->security->getUser();

        if (!$committee instanceof Committee || !$user instanceof Adherent) {
            return;
        }

        if ($request->isMethod(Request::METHOD_PUT)) {
            $this->dispatcher->dispatch(
                new UserCommitteeActionEvent($user, $committee),
                UserActionEvents::USER_COMMITTEE_BEFORE_UPDATE
            );
        }
    }

    public function onCommitteeWrite(ViewEvent $viewEvent): void
    {
        $committee = $viewEvent->getControllerResult();
        $request = $viewEvent->getRequest();
        $user = $this->security->getUser();

        if (!$committee instanceof Committee || !$user instanceof Adherent) {
            return;
        }

        $event = new UserCommitteeActionEvent($user, $committee);

        match ($request->getMethod()) {
            Request::METHOD_POST => $this->dispatcher->dispatch($event, UserActionEvents::USER_COMMITTEE_CREATE),
            Request::METHOD_PUT => $this->dispatcher->dispatch($event, UserActionEvents::USER_COMMITTEE_AFTER_UPDATE),
            Request::METHOD_DELETE => $this->dispatcher->dispatch($event, UserActionEvents::USER_COMMITTEE_DELETE),
            default => null,
        };
    }
}
