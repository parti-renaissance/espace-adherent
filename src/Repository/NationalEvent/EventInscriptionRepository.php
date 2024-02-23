<?php

namespace App\Repository\NationalEvent;

use App\Entity\Adherent;
use App\Entity\NationalEvent\EventInscription;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EventInscriptionRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventInscription::class);
    }

    /**
     * @return EventInscription[]
     */
    public function findAllByAdherent(Adherent $adherent): array
    {
        return $this->createQueryBuilder('ei')
            ->addSelect('e')
            ->innerJoin('ei.event', 'e')
            ->where('ei.adherent = :adherent')
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->getResult()
        ;
    }
}
