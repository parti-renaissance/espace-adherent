<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\Agora;
use App\Entity\AgoraMembership;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AgoraMembershipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgoraMembership::class);
    }

    public function findMembership(Agora $agora, Adherent $adherent): ?AgoraMembership
    {
        return $this
            ->createQueryBuilder('agora_membership')
            ->andWhere('agora_membership.agora = :agora')
            ->andWhere('agora_membership.adherent = :adherent')
            ->setParameters([
                'agora' => $agora,
                'adherent' => $adherent,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
