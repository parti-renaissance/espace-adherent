<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\AdherentChangeEmailToken;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AdherentChangeEmailTokenRepository extends AbstractAdherentTokenRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AdherentChangeEmailToken::class);
    }

    public function findLastUnusedByAdherent(Adherent $adherent): ?AdherentChangeEmailToken
    {
        return $this
            ->createQueryForLastUnused('token')
            ->andWhere('token.adherentUuid = :uuid')
            ->setParameter('uuid', $adherent->getUuidAsString())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findLastUnusedByEmail(string $emailAddress): ?AdherentChangeEmailToken
    {
        return $this
            ->createQueryForLastUnused('token')
            ->andWhere('token.email = :email')
            ->setParameter('email', $emailAddress)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function invalidateOtherActiveToken(Adherent $adherent, AdherentChangeEmailToken $token): void
    {
        $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->update(AdherentChangeEmailToken::class, 'token')
            ->set('token.expiredAt', ':date')
            ->where('token.adherentUuid = :uuid AND token.usedAt IS NULL AND token.id != :last_token')
            ->getQuery()
            ->execute([
                'date' => new \DateTime('-1 second'),
                'uuid' => $adherent->getUuidAsString(),
                'last_token' => $token->getId(),
            ])
        ;
    }

    private function createQueryForLastUnused(string $alias): QueryBuilder
    {
        return $this
            ->createQueryBuilder($alias)
            ->where("${alias}.usedAt IS NULL AND ${alias}.expiredAt >= :date")
            ->setParameter('date', new \DateTime())
            ->setMaxResults(1)
            ->orderBy("${alias}.createdAt", 'DESC')
        ;
    }
}
