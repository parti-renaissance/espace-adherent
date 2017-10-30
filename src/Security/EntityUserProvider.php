<?php

namespace AppBundle\Security;

use AppBundle\Membership\AdherentFactory;
use Doctrine\Common\Persistence\ManagerRegistry;
use GuzzleHttp\Client;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\EntityUserProvider as BaseProvider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class EntityUserProvider extends BaseProvider
{
    private $authClient;
    private $adherentFactory;

    public function __construct(
        ManagerRegistry $registry,
        $class,
        array $properties,
        $managerName = null,
        Client $authClient,
        AdherentFactory $adherentFactory
    ) {
        parent::__construct($registry, $class, $properties, $managerName);

        $this->authClient = $authClient;
        $this->adherentFactory = $adherentFactory;
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        try {
            return parent::loadUserByOAuthUserResponse($response);
        } catch (UsernameNotFoundException $e) {
            $result = $this->authClient->request(
                'GET',
                sprintf('/api/users/%s', $response->getResponse()['uuid']), ['CONTENT_TYPE' => 'application/json']
            );

            if (null === $result) {
                throw $e;
            }

            $userData = (array) \GuzzleHttp\json_decode($result->getBody()->getContents());
            $adherent = $this->adherentFactory->createFromAPIResponse($userData);

            $this->em->persist($adherent);
            $this->em->flush();

            return $adherent;
        }
    }
}
