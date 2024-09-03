<?php

namespace App\Security\Listener;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Renaissance\App\UrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class InactiveAdminListener
{
    public function __construct(
        private readonly SessionInterface $session,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly UrlGenerator $urlGenerator,
        private readonly int $maxIdleTime = 0,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$token = $this->tokenStorage->getToken()) {
            return;
        }

        $user = $token->getUser();

        $isPreviousAdmin = false;
        foreach ($token->getRoleNames() as $role) {
            if ('ROLE_PREVIOUS_ADMIN' == $role) {
                $isPreviousAdmin = true;

                break;
            }
        }

        if ($this->maxIdleTime > 0 && ($user instanceof Administrator || ($user instanceof Adherent && $isPreviousAdmin))) {
            $lapse = time() - $this->session->getMetadataBag()->getLastUsed();

            if ($lapse > $this->maxIdleTime) {
                $this->tokenStorage->setToken(null);

                $event->setResponse(new RedirectResponse($this->urlGenerator->generateLogout()));
            }
        }
    }
}
