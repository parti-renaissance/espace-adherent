<?php

namespace App\Repository\Biography;

use App\Collection\ExecutiveOfficeMemberCollection;
use App\Entity\Biography\ExecutiveOfficeMember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ExecutiveOfficeMemberRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ExecutiveOfficeMember::class);
    }

    public function findAllPublishedMembers(): ExecutiveOfficeMemberCollection
    {
        $allMembers = $this
            ->createPublishedQueryBuilder()
            ->orderBy('executiveOfficeMember.lastName', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        return $this->createExecutiveOfficeMemberCollection($allMembers);
    }

    public function findOneExecutiveOfficerMember(bool $published = true): ?ExecutiveOfficeMember
    {
        return $this
            ->createPublishedQueryBuilder($published)
            ->andWhere('executiveOfficeMember.executiveOfficer = true')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneDeputyGeneralDelegateMember(bool $published = true): ?ExecutiveOfficeMember
    {
        return $this
            ->createPublishedQueryBuilder($published)
            ->andWhere('executiveOfficeMember.deputyGeneralDelegate = true')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOnePublishedBySlug(string $slug): ?ExecutiveOfficeMember
    {
        return $this
            ->createPublishedQueryBuilder()
            ->andWhere('executiveOfficeMember.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    private function createPublishedQueryBuilder(bool $published = true): QueryBuilder
    {
        return $this
            ->createQueryBuilder('executiveOfficeMember')
            ->andWhere('executiveOfficeMember.published = :published')
            ->setParameter('published', $published)
        ;
    }

    private function createExecutiveOfficeMemberCollection(array $allMembers): ExecutiveOfficeMemberCollection
    {
        return new ExecutiveOfficeMemberCollection($allMembers);
    }
}
