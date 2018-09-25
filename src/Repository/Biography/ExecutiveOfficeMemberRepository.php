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

    public function findOneExecutiveOfficerMember(bool $published = true): ?ExecutiveOfficeMember
    {
        return $this->createQueryBuilder('executiveOfficer')
            ->andWhere('executiveOfficer.published = :published')
            ->andWhere('executiveOfficer.executiveOfficer = :isExecutiveOfficer')
            ->setParameter('published', $published)
            ->setParameter('isExecutiveOfficer', true)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findAllExecutiveOfficeMembers(): ExecutiveOfficeMemberCollection
    {
        $allMembers = $this->createQueryBuilder('executiveOfficeMember')
            ->andWhere('executiveOfficeMember.published = :published')
            ->setParameter('published', true)
            ->getQuery()
            ->getResult()
        ;

        return $this->createExecutiveOfficeMemberCollection($allMembers);
    }

    private function createExecutiveOfficeMemberCollection(array $allMembers): ExecutiveOfficeMemberCollection
    {
        return new ExecutiveOfficeMemberCollection($allMembers);
    }
}
