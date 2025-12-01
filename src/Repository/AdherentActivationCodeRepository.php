<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\AdherentActivationCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\AdherentActivationCode>
 */
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
            ->setParameters(new ArrayCollection([new Parameter('adherent', $adherent), new Parameter('code', $code)]))
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function invalidateForAdherent(Adherent $user): void
    {
        $this->createQueryBuilder('code')
            ->update()
            ->where('code.adherent = :adherent')
            ->andWhere('code.usedAt IS NULL AND code.revokedAt IS NULL')
            ->set('code.revokedAt', ':now')
            ->setParameters(new ArrayCollection([new Parameter('adherent', $user), new Parameter('now', new \DateTime())]))
            ->getQuery()
            ->execute()
        ;
    }
}
