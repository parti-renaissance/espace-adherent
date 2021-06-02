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

    public function getForExport(CauseFilter $filter): array
    {
        return $this->createFilterQueryBuilder($filter)->getQuery()->getResult();
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
            ->leftJoin('cause.coalition', 'coalition')
            ->leftJoin('cause.author', 'author')
            ->orderBy('cause.'.$filter->getSort(), 'd' === $filter->getOrder() ? 'DESC' : 'ASC')
        ;

        if ($filter->getStatus()) {
            $qb
                ->andWhere('cause.status = :status')
                ->setParameter('status', $filter->getStatus())
            ;
        }

        if ($filter->getPrimaryCoalition()) {
            $qb
                ->andWhere('cause.coalition = :primaryCoalition')
                ->setParameter('primaryCoalition', $filter->getPrimaryCoalition())
            ;
        }

        if ($filter->getSecondaryCoalition()) {
            $qb
                ->andWhere('cause.secondCoalition = :secondaryCoalition')
                ->setParameter('secondaryCoalition', $filter->getSecondaryCoalition())
            ;
        }

        if ($filter->getName()) {
            $qb
                ->andWhere('cause.name LIKE :name')
                ->setParameter('name', '%'.$filter->getName().'%')
            ;
        }

        if ($filter->getAuthorFirstName()) {
            $qb
                ->andWhere('author.firstName LIKE :authorFirstName')
                ->setParameter('authorFirstName', '%'.$filter->getAuthorFirstName().'%')
            ;
        }

        if ($filter->getAuthorLastName()) {
            $qb
                ->andWhere('author.lastName LIKE :authorLastName')
                ->setParameter('authorLastName', '%'.$filter->getAuthorLastName().'%')
            ;
        }

        if ($filter->getCreatedAfter()) {
            $qb
                ->andWhere('cause.createdAt >= :createdAfter')
                ->setParameter('createdAfter', $filter->getCreatedAfter())
            ;
        }

        if ($filter->getCreatedBefore()) {
            $qb
                ->andWhere('cause.createdAt <= :createdBefore')
                ->setParameter('createdBefore', $filter->getCreatedBefore())
            ;
        }

        return $qb;
    }

    public function findByFollower(string $email, bool $isAdherent): array
    {
        $qb = $this->createQueryBuilder('cause')
            ->join('cause.followers', 'follower')
            ->where('cause.status = :approved')
            ->setParameters([
                'approved' => Cause::STATUS_APPROVED,
                'email' => $email,
            ])
        ;

        if ($isAdherent) {
            $qb->join('follower.adherent', 'adherent')
                ->andWhere('adherent.emailAddress = :email')
            ;
        } else {
            $qb->andWhere('follower.emailAddress = :email');
        }

        return $qb->getQuery()->getResult();
    }
}
