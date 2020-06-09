<?php

namespace App\EventSubscriber;

use App\Entity\Adherent;
use App\Repository\MyTeam\DelegatedAccessRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DelegatedAccessSubscriber implements EventSubscriberInterface
{
    /** @var DelegatedAccessRepository */
    private $delegatedAccessRepository;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var RequestStack */
    private $requestStack;

    public function __construct(
        DelegatedAccessRepository $delegatedAccessRepository,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack
    ) {
        $this->delegatedAccessRepository = $delegatedAccessRepository;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['setDelegatedAccessesInRequest', 2], // needs to be called before api platform's DenyAccessListener (priotity = 1)
            KernelEvents::CONTROLLER => 'selectCurrentDelegatedAccess',
        ];
    }

    public function setDelegatedAccessesInRequest(GetResponseEvent $event)
    {
        if ($this->requestStack->getMasterRequest() !== $event->getRequest() && !$event->getRequest()->isXmlHttpRequest()) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return;
        }

        $adherent = $token->getUser();

        if (!$adherent instanceof Adherent) {
            return;
        }

        $event->getRequest()->attributes->set('delegated_accesses', $this->delegatedAccessRepository->findAllDelegatedAccessForUser($adherent));
    }

    public function selectCurrentDelegatedAccess(FilterControllerEvent $event): void
    {
        if ($this->requestStack->getMasterRequest() !== $event->getRequest() && !$event->getRequest()->isXmlHttpRequest()) {
            return;
        }

        $route = $event->getRequest()->attributes->get('_route');

        if (false !== \strpos($route, '_delegated')) {
            switch (true) {
                case false !== \strpos($route, 'deputy'):
                    $type = 'deputy';
                    break;
                case false !== \strpos($route, 'senator'):
                    $type = 'senator';
                    break;
                case false !== \strpos($route, 'referent'):
                    $type = 'referent';
                    break;
                default:
                    return;
            }

            foreach ($event->getRequest()->attributes->get('delegated_accesses', []) as $delegatedAccess) {
                if ($delegatedAccess->getType() === $type) {
                    $event->getRequest()->attributes->set('delegated_access', $delegatedAccess);
                    break;
                }
            }
        }
    }
}
