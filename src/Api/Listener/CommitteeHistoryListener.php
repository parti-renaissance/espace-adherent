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
                ['onPreWrite', EventPriorities::PRE_WRITE],
                ['onPostWrite', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function onPreWrite(ViewEvent $viewEvent): void
    {
        $request = $viewEvent->getRequest();
        $committee = $viewEvent->getControllerResult();
        $user = $this->security->getUser();

        if (
            !$committee instanceof Committee
            || !$user instanceof Adherent
            || !$request->isMethod(Request::METHOD_PUT)
        ) {
            return;
        }

        $original = $request->attributes->get('original_data');

        if ($original instanceof Committee) {
            $this->dispatcher->dispatch(
                new UserCommitteeActionEvent($user, clone $original),
                UserActionEvents::USER_COMMITTEE_BEFORE_UPDATE
            );
        }
    }

    public function onPostWrite(ViewEvent $viewEvent): void
    {
        $request = $viewEvent->getRequest();
        $committee = $viewEvent->getControllerResult();
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
