<?php

declare(strict_types=1);

namespace App\Repository\Poll;

use App\Entity\Geo\Zone;
use App\Entity\Poll\Choice;
use App\Entity\Poll\LocalPoll;
use App\Entity\Poll\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\Persistence\ManagerRegistry;

class LocalPollRepository extends ServiceEntityRepository
{
    use UnpublishPollTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocalPoll::class);
    }

    /**
     * @return LocalPoll[]
     */
    public function findAllByZonesWithStats(array $zones): array
    {
        $qb = $this
            ->createQueryBuilder('poll')
            ->innerJoin('poll.zone', 'zone')
            ->addSelect('zone')
            ->addSelect(\sprintf('(
                SELECT COUNT(vote_y.id) FROM %s AS vote_y
                INNER JOIN vote_y.choice AS choice_y
                WHERE choice_y.value = :yes AND choice_y.poll = poll
            ) AS yes_count', Vote::class))
            ->addSelect(\sprintf('(
                SELECT COUNT(vote_n.id) FROM %s AS vote_n
                INNER JOIN vote_n.choice AS choice_n
                WHERE choice_n.value = :no AND choice_n.poll = poll
            ) AS no_count', Vote::class))
            ->where('(zone IN (:zones))')
            ->setParameter('zones', $zones)
            ->setParameter('yes', Choice::YES)
            ->setParameter('no', Choice::NO)
            ->orderBy('poll.createdAt', 'DESC')
        ;

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOnePublishedByZone(Zone $region, ?Zone $department = null, ?string $postalCode = null): ?LocalPoll
    {
        $qb = $this->createQueryBuilder('poll')
            ->select('poll')
            ->addSelect('
            CASE
                WHEN zone.type = :zone_region THEN 1
                WHEN zone.type = :zone_department THEN 2
                ELSE 3
            END AS HIDDEN priority
            ')
            ->leftJoin('poll.zone', 'zone')
            ->where('poll.published = :true AND poll.finishAt > :now')
        ;

        $conditions = (new Orx())
            ->add('zone.type = :zone_region AND zone = :region')
        ;

        if ($department) {
            $conditions->add('zone.type = :zone_department AND zone = :department');
            $qb->setParameter('department', $department);
        }

        if ($postalCode) {
            $conditions->add('zone.type = :zone_borough AND zone.postalCode = :postal_code');
            $qb
                ->setParameter('zone_borough', Zone::BOROUGH)
                ->setParameter('postal_code', $postalCode)
            ;
        }

        return $qb
            ->andWhere($conditions)
            ->addOrderBy('priority', 'asc')
            ->addOrderBy('poll.finishAt', 'desc')
            ->setParameter('region', $region)
            ->setParameter('zone_region', Zone::REGION)
            ->setParameter('zone_department', Zone::DEPARTMENT)
            ->setParameter('true', true)
            ->setParameter('now', new \DateTime())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
