<?php

namespace AppBundle\Repository\OAuth;

use AppBundle\Entity\OAuth\AuthorizationCode;
use Doctrine\ORM\EntityRepository;
use Ramsey\Uuid\UuidInterface;

class AuthorizationCodeRepository extends EntityRepository
{
    public function save(AuthorizationCode $token): void
    {
        $this->_em->persist($token);
        $this->_em->flush();
    }

    public function findAuthorizationCodeByIdentifier(string $identifier): ?AuthorizationCode
    {
        return $this->findOneBy(['identifier' => $identifier]);
    }

    public function findAuthorizationCodeByUuid(UuidInterface $uuid): ?AuthorizationCode
    {
        return $this->findOneBy(['uuid' => $uuid->toString()]);
    }
}
