<?php

namespace App\Repository;

use App\Entity\Administrator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AdministratorRepository extends ServiceEntityRepository implements UserLoaderInterface, UserProviderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Administrator::class);
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $admin = $this->createActivatedQueryBuilder('a')
            ->addSelect('role')
            ->leftJoin('a.administratorRoles', 'role')
            ->andWhere('a.emailAddress = :email')
            ->setParameter('email', $identifier)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$admin) {
            throw new UserNotFoundException(\sprintf('User "%s" not found.', $identifier));
        }

        return $admin;
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

    public function refreshUser(UserInterface $user): UserInterface
    {
        $class = $user::class;
        $username = $user->getUserIdentifier();

        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(\sprintf('User of type "%s" and identified by "%s" is not supported by this provider.', $class, $username));
        }

        return $this->loadUserByIdentifier($username);
    }

    public function supportsClass(string $class): bool
    {
        return Administrator::class === $class;
    }
}
