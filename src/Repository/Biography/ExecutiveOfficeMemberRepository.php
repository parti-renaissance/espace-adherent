<?php

namespace AppBundle\Repository\Biography;

use AppBundle\Collection\ExecutiveOfficeMemberCollection;
use AppBundle\Entity\Biography\ExecutiveOfficeMember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ExecutiveOfficeMemberRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ExecutiveOfficeMember::class);
    }

    public function findAllPublishedMembers(): ExecutiveOfficeMemberCollection
    {
        $allMembers = $this->createQueryBuilder('executiveOfficeMember')
            ->andWhere('executiveOfficeMember.published = :published')
            ->setParameter('published', true)
            ->orderBy('executiveOfficeMember.lastName', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        return $this->createExecutiveOfficeMemberCollection($allMembers);
    }

    public function findOneExecutiveOfficerMember(bool $published = true): ?ExecutiveOfficeMember
    {
        return $this->createQueryBuilder('executiveOfficer')
            ->andWhere('executiveOfficer.published = :published')
            ->andWhere('executiveOfficer.executiveOfficer = true')
            ->setParameter('published', $published)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOnePublishedBySlug(string $slug): ?ExecutiveOfficeMember
    {
        return $this->createQueryBuilder('executiveOfficeMember')
            ->andWhere('executiveOfficeMember.slug = :slug')
            ->andWhere('executiveOfficeMember.published = true')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    private function createExecutiveOfficeMemberCollection(array $allMembers): ExecutiveOfficeMemberCollection
    {
        return new ExecutiveOfficeMemberCollection($allMembers);
    }
}
