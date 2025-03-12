<?php

namespace App\Security;

use App\Entity\Adherent;
use App\Security\Http\Session\AnonymousFollowerSession;
use Symfony\Bundle\SecurityBundle\Security as SfSecurity;
use Symfony\Component\HttpFoundation\Response;

class AdherentLogin
{
    public function __construct(
        private readonly SfSecurity $decoratedSecurity,
        private readonly AnonymousFollowerSession $anonymousFollowerSession,
    ) {
    }

    public function login(Adherent $user): ?Response
    {
        $callback = null;
        if ($this->anonymousFollowerSession->isStarted()) {
            $callback = $this->anonymousFollowerSession->getCallback();
        }

        $response = $this->decoratedSecurity->login($user, 'form_login');

        if (!empty($callback)) {
            $this->anonymousFollowerSession->setCallback($callback);
        }

        return $response;
    }
}
