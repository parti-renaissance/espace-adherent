<?php

namespace AppBundle\OAuth\Repository;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class OAuthUserRepository implements UserRepositoryInterface
{
    private $authenticationManager;

    public function __construct(AuthenticationManagerInterface $authenticationManager)
    {
        $this->authenticationManager = $authenticationManager;
    }

    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {
        try {
            return $this->authenticationManager->authenticate(new UsernamePasswordToken($username, $password, 'main'))->getUser();
        } catch (AuthenticationException $e) {
        }

        return null;
    }
}
