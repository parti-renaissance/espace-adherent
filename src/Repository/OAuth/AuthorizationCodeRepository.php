<?php

namespace App\Repository\OAuth;

use App\Entity\OAuth\AuthorizationCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AuthorizationCodeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AuthorizationCode::class);
    }

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
