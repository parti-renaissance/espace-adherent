<?php

namespace App\Repository\Biography;

use App\Collection\ExecutiveOfficeMemberCollection;
use App\Entity\Biography\ExecutiveOfficeMember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ExecutiveOfficeMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExecutiveOfficeMember::class);
    }

    public function findAllPublishedMembers(bool $forRenaissance = false): ExecutiveOfficeMemberCollection
    {
        $allMembers = $this
            ->createPublishedQueryBuilder(true, $forRenaissance)
            ->orderBy('executiveOfficeMember.lastName', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        return $this->createExecutiveOfficeMemberCollection($allMembers);
    }

    public function findOneExecutiveOfficerMember(
        bool $published = true,
        bool $forRenaissance = false
    ): ?ExecutiveOfficeMember {
        return $this
            ->createPublishedQueryBuilder($published, $forRenaissance)
            ->andWhere('executiveOfficeMember.executiveOfficer = true')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOnePresidentMember(bool $published = true, bool $forRenaissance = false): ?ExecutiveOfficeMember
    {
        return $this
            ->createPublishedQueryBuilder($published, $forRenaissance)
            ->andWhere('executiveOfficeMember.president = true')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneDeputyGeneralDelegateMember(
        bool $published = true,
        bool $forRenaissance = false
    ): ?ExecutiveOfficeMember {
        return $this
            ->createPublishedQueryBuilder($published, $forRenaissance)
            ->andWhere('executiveOfficeMember.deputyGeneralDelegate = true')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOnePublishedBySlug(string $slug, bool $forRenaissance = false): ?ExecutiveOfficeMember
    {
        return $this
            ->createPublishedQueryBuilder(true, $forRenaissance)
            ->andWhere('executiveOfficeMember.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    private function createPublishedQueryBuilder(bool $published = true, bool $forRenaissance = false): QueryBuilder
    {
        return $this
            ->createQueryBuilder('executiveOfficeMember')
            ->andWhere('executiveOfficeMember.published = :published')
            ->setParameter('published', $published)
            ->andWhere('executiveOfficeMember.forRenaissance = :for_renaissance')
            ->setParameter('for_renaissance', $forRenaissance)
        ;
    }

    private function createExecutiveOfficeMemberCollection(array $allMembers): ExecutiveOfficeMemberCollection
    {
        return new ExecutiveOfficeMemberCollection($allMembers);
    }
}
