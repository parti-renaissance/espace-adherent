<?php

namespace App\Repository;

use App\Entity\Administrator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AdministratorRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Administrator::class);
    }

    public function loadUserByIdentifier(string $identifier): ?UserInterface
    {
        $query = $this
            ->createActivatedQueryBuilder('a')
            ->andWhere('a.emailAddress = :email')
            ->setParameter('email', $identifier)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    /**
     * @return array|Administrator[]
     */
    public function findWithRole(string $role): array
    {
        return $this
            ->createActivatedQueryBuilder('a')
            ->innerJoin('a.administratorRoles', 'role')
            ->andWhere('role.code = :role_code')
            ->setParameter('role_code', $role)
            ->getQuery()
            ->getResult()
        ;
    }

    private function createActivatedQueryBuilder(string $alias): QueryBuilder
    {
        return $this
            ->createQueryBuilder($alias)
            ->where("$alias.activated = true")
        ;
    }
}
