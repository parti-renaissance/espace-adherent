<?php

declare(strict_types=1);

namespace App\Repository\OAuth;

use App\Entity\OAuth\AuthorizationCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\OAuth\AuthorizationCode>
 */
class AuthorizationCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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
