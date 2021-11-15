<?php

namespace App\Jecoute\Api\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Adherent;
use App\Entity\Jecoute\News;
use App\Jecoute\JecouteSpaceEnum;
use App\Jecoute\NewsHandler;
use App\Scope\AuthorizationChecker;
use App\Scope\ScopeEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

class PreWriteNewsListener implements EventSubscriberInterface
{
    private Security $security;
    private NewsHandler $handler;
    private RequestStack $requestStack;
    private AuthorizationChecker $authorizationChecker;

    public function __construct(
        Security $security,
        NewsHandler $handler,
        RequestStack $requestStack,
        AuthorizationChecker $authorizationChecker
    ) {
        $this->security = $security;
        $this->handler = $handler;
        $this->requestStack = $requestStack;
        $this->authorizationChecker = $authorizationChecker;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::VIEW => ['preWrite', EventPriorities::PRE_WRITE]];
    }

    public function preWrite(ViewEvent $event): void
    {
        $news = $event->getControllerResult();

        if (!$news instanceof News
            || !$event->getRequest()->isMethod(Request::METHOD_POST)
        ) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof Adherent) {
            throw new \RuntimeException('User is not a connected adherent');
        }
        $news->setAuthor($this->security->getUser());

        $scope = $this->authorizationChecker->getScope($this->requestStack->getMasterRequest());
        if (ScopeEnum::REFERENT === $scope) {
            $news->setSpace(JecouteSpaceEnum::REFERENT_SPACE);
        }

        $this->handler->buildTopic($news);
    }
}
