<?php

namespace App\Security;

use App\Entity\Adherent;
use App\Entity\Administrator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;

class InactiveAdminListener
{
    private $session;
    private $tokenStorage;
    private $logoutUrlGenerator;
    private $maxIdleTime;

    public function __construct(
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        LogoutUrlGenerator $logoutUrlGenerator,
        int $maxIdleTime = 0
    ) {
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->logoutUrlGenerator = $logoutUrlGenerator;
        $this->maxIdleTime = $maxIdleTime;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        if ($token = $this->tokenStorage->getToken()) {
            $user = $token->getUser();

            $isPreviousAdmin = false;
            foreach ($token->getRoles() as $role) {
                if ($role instanceof Role && 'ROLE_PREVIOUS_ADMIN' == $role->getRole()) {
                    $isPreviousAdmin = true;

                    break;
                }
            }

            if ($this->maxIdleTime > 0 &&
                ($user instanceof Administrator || ($user instanceof Adherent && $isPreviousAdmin))) {
                $lapse = time() - $this->session->getMetadataBag()->getLastUsed();

                if ($lapse > $this->maxIdleTime) {
                    $this->tokenStorage->setToken(null);

                    $event->setResponse(new RedirectResponse($this->logoutUrlGenerator->getLogoutPath()));
                }
            }
        }
    }
}
