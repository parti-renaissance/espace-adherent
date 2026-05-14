<?php

declare(strict_types=1);

namespace App\Repository\OAuth;

use App\Entity\OAuth\AuthorizationCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class AuthorizationCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthorizationCode::class);
    }

    public function save(AuthorizationCode $token): void
    {
        $this->getEntityManager()->persist($token);
        $this->getEntityManager()->flush();
    }

    public function findAuthorizationCodeByIdentifier(string $identifier): ?AuthorizationCode
    {
        return $this->findOneBy(['identifier' => $identifier]);
    }

    public function findAuthorizationCodeByUuid(Uuid $uuid): ?AuthorizationCode
    {
        return $this->findOneBy(['uuid' => $uuid->toRfc4122()]);
    }
}
