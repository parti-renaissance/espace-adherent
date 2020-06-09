<?php

namespace App\Repository;

use App\Entity\UserListDefinition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;

class UserListDefinitionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserListDefinition::class);
    }

    public function getForType(string $type): array
    {
        return $this->createQueryBuilder('userListDefinition')
            ->where('userListDefinition.type = :type')
            ->setParameter('type', $type)
            ->orderBy('userListDefinition.label', 'ASC')
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function getMemberIdsForType(string $type, array $ids, string $joinClass): array
    {
        return $this->createQueryBuilder('userListDefinition')
            ->leftJoin(
                $joinClass,
                'members',
                Join::WITH,
                'userListDefinition MEMBER OF members.userListDefinitions'
            )
            ->select('userListDefinition.code AS code, GROUP_CONCAT(members.id) AS ids')
            ->where('userListDefinition.type = :type')
            ->andWhere('members.id IN (:ids)')
            ->setParameter('type', $type)
            ->setParameter('ids', $ids)
            ->groupBy('userListDefinition.code')
            ->getQuery()
            ->getArrayResult()
        ;
    }
}
