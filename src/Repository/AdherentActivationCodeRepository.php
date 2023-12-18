<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\AdherentActivationCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AdherentActivationCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentActivationCode::class);
    }

    public function findOneByCode(string $code, Adherent $adherent): ?AdherentActivationCode
    {
        return $this->createQueryBuilder('code')
            ->where('code.adherent = :adherent')
            ->andWhere('code.value = :code')
            ->setParameters([
                'adherent' => $adherent,
                'code' => $code,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function deleteForAdherent(Adherent $user): void
    {
        $this->createQueryBuilder('code')
            ->update()
            ->where('code.adherent = :adherent')
            ->andWhere('code.usedAt IS NULL')
            ->set('code.usedAt', ':now')
            ->setParameters([
                'adherent' => $user,
                'now' => new \DateTime(),
            ])
            ->getQuery()
            ->execute()
        ;
    }
}
