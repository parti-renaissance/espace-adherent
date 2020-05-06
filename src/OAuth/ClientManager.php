<?php

namespace App\OAuth;

use App\Entity\OAuth\Client;
use Doctrine\ORM\EntityManagerInterface;

class ClientManager
{
    private $em;
    private $tokenRevocationAuthority;

    public function __construct(EntityManagerInterface $em, TokenRevocationAuthority $tokenRevocationAuthority)
    {
        $this->em = $em;
        $this->tokenRevocationAuthority = $tokenRevocationAuthority;
    }

    public function delete(Client $client): void
    {
        $this->em->transactional(function () use ($client) {
            $this->em->remove($client);
            $this->tokenRevocationAuthority->revokeClientTokens($client);
        });
    }
}
