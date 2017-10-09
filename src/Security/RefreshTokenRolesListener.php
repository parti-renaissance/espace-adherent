<?php

namespace AppBundle\Security;

use AppBundle\Entity\Adherent;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author Dimitri Gritsajuk <dimitri.gritsajuk@sensiolabs.com>
 */
class RefreshTokenRolesListener
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelResponse(FilterResponseEvent $event): void
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if (null === $token || !$token instanceof OAuthToken) {
            return;
        }

        $user = $token->getUser();

        if (!$user instanceof Adherent) {
            return;
        }

        if ($this->compareRoles($token->getRoles(), $user->getRoles())) {
            return;
        }

        $newToken = new OAuthToken($token->getRawToken(), $user->getRoles());
        $newToken->setResourceOwnerName($token->getResourceOwnerName());
        $newToken->setUser($user);
        $newToken->setAuthenticated(true);

        $this->tokenStorage->setToken($newToken);
    }

    /**
     * Compares token's roles with user's roles.
     *
     * @param array $fromToken
     * @param array $userRoles
     *
     * @return bool
     */
    private function compareRoles(array $fromToken, array $userRoles): bool
    {
        $tokenRoles = [];
        foreach ($fromToken as $role) {
            $tokenRoles[] = $role->getRole();
        }

        return count($tokenRoles) === count($userRoles)
            && array_diff($tokenRoles, $userRoles) === array_diff($userRoles, $tokenRoles);
    }
}
