<?php

namespace App\Repository\Coalition;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Coalition\Filter\CauseFilter;
use App\Entity\Coalition\Cause;
use App\Repository\PaginatorTrait;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class CauseRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;
    use PaginatorTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cause::class);
    }

    public function findFollowedByUuids(array $uuids, UserInterface $user): array
    {
        self::validUuids($uuids);

        return $this->createQueryBuilder('cause')
            ->innerJoin('cause.followers', 'follower')
            ->andWhere('follower.adherent = :adherent')
            ->andWhere('cause.uuid IN (:uuids)')
            ->setParameter('adherent', $user)
            ->setParameter('uuids', $uuids)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Cause[]|PaginatorInterface
     */
    public function searchByFilter(CauseFilter $filter, int $page = 1, int $limit = 100): PaginatorInterface
    {
        return $this->configurePaginator($this->createFilterQueryBuilder($filter), $page, $limit);
    }

    public function countCauses(): int
    {
        return (int) $this
            ->createQueryBuilder('cause')
            ->select('COUNT(1)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return Cause[]
     */
    public function getByIds(array $ids): array
    {
        return $this->findBy(['id' => $ids]);
    }

    public function getStatistics(): array
    {
        $stats = $this->createQueryBuilder('cause')
            ->leftJoin('cause.followers', 'follower')
            ->select('COUNT(DISTINCT cause.id) as total')
            ->addSelect('COUNT(DISTINCT follower.id) as total_followers')
            ->where('cause.status = :approved')
            ->setParameter('approved', Cause::STATUS_APPROVED)
            ->getQuery()
            ->getScalarResult()
        ;

        return $stats[0];
    }

    private function createFilterQueryBuilder(CauseFilter $filter): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('cause')
            ->orderBy('cause.'.$filter->getSort(), 'd' === $filter->getOrder() ? 'DESC' : 'ASC')
        ;

        if ($filter->getStatus()) {
            $qb
                ->where('cause.status = :status')
                ->setParameter('status', $filter->getStatus())
            ;
        }

        return $qb;
    }
}
