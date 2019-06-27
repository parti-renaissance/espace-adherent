<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AdministratorRepository extends EntityRepository implements UserLoaderInterface
{
    /**
     * Loads the user for the given username.
     *
     * This method must return null if the user is not found.
     *
     * @param string $username The username
     *
     * @return UserInterface|null
     */
    public function loadUserByUsername($username)
    {
        $query = $this
            ->createQueryBuilder('a')
            ->where('a.emailAddress = :username')
            ->andWhere('a.activated = true')
            ->setParameter('username', $username)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }
}
