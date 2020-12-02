<?php

namespace App\Repository\TerritorialCouncil;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\Convocation;
use App\Repository\PaginatorTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ConvocationRepository extends ServiceEntityRepository
{
    use PaginatorTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Convocation::class);
    }

    /**
     * @return Convocation[]|PaginatorInterface
     */
    public function getPaginator(Adherent $adherent, int $page = 1, int $limit = 30): PaginatorInterface
    {
        $qb = $this->createQueryBuilder('convocation')
            ->addSelect('political_committee', 'territorial_council')
            ->leftJoin('convocation.territorialCouncil', 'territorial_council')
            ->leftJoin('convocation.politicalCommittee', 'political_committee')
            ->andWhere('convocation.createdBy = :author')
            ->setParameter('author', $adherent)
            ->orderBy('convocation.meetingStartDate', 'DESC')
        ;

        return $this->configurePaginator($qb, $page, $limit);
    }
}
