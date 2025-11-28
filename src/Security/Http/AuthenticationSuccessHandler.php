<?php

declare(strict_types=1);

namespace App\Security\Http;

use App\Entity\Adherent;
use App\Security\Http\Session\AnonymousFollowerSession;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Contracts\Service\Attribute\Required;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    private AnonymousFollowerSession $anonymousFollowerSession;
    private EntityManagerInterface $manager;

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        if (!$token instanceof NullToken && $this->anonymousFollowerSession->isStarted()) {
            return $this->anonymousFollowerSession->terminate();
        }

        $user = $token->getUser();

        // Only record adherent logins
        if ($user instanceof Adherent) {
            $user->recordLastLoginTime();
            $this->manager->flush();
        }

        return parent::onAuthenticationSuccess($request, $token);
    }

    #[Required]
    public function setAnonymousFollowerSession(AnonymousFollowerSession $anonymousFollowerSession): void
    {
        $this->anonymousFollowerSession = $anonymousFollowerSession;
    }

    #[Required]
    public function setManager(EntityManagerInterface $manager): void
    {
        $this->manager = $manager;
    }
}
