<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\Agora;
use App\Entity\AgoraMembership;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\AgoraMembership>
 */
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
            ->setParameters(new ArrayCollection([new Parameter('agora', $agora), new Parameter('adherent', $adherent)]))
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
