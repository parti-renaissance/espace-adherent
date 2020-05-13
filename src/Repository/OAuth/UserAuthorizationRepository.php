<?php

namespace App\Repository\OAuth;

use App\Entity\Adherent;
use App\Entity\OAuth\Client;
use App\Entity\OAuth\UserAuthorization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class UserAuthorizationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserAuthorization::class);
    }

    /**
     * @return UserAuthorization[]
     */
    public function findByUser(Adherent $user): array
    {
        return $this->findByUserUuid($user->getUuid());
    }

    /**
     * @return UserAuthorization[]
     */
    public function findByUserUuid(UuidInterface $userUuid): array
    {
        return $this
            ->createQueryBuilder('ua')
            ->select('ua, c')
            ->leftJoin('ua.client', 'c')
            ->leftJoin('ua.user', 'u')
            ->where('u.uuid = :uuid')
            ->orderBy('ua.id', 'DESC')
            ->setParameter('uuid', $userUuid->toString())
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByUserAndClient(Adherent $user, Client $client): ?UserAuthorization
    {
        return $this->findOneBy(['user' => $user, 'client' => $client]);
    }

    public function findByUuid(UuidInterface $uuid): ?UserAuthorization
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    public function save(UserAuthorization $userAuthorization): void
    {
        $this->_em->persist($userAuthorization);
        $this->_em->flush($userAuthorization);
    }

    public function delete(UserAuthorization $userAuthorization): void
    {
        $this->_em->remove($userAuthorization);
        $this->_em->flush($userAuthorization);
    }
}
