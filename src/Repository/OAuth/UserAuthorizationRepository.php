<?php

declare(strict_types=1);

namespace App\Repository\OAuth;

use App\Entity\Adherent;
use App\Entity\OAuth\Client;
use App\Entity\OAuth\UserAuthorization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class UserAuthorizationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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
    public function findByUserUuid(Uuid $userUuid): array
    {
        return $this
            ->createQueryBuilder('ua')
            ->select('ua, c')
            ->leftJoin('ua.client', 'c')
            ->leftJoin('ua.user', 'u')
            ->where('u.uuid = :uuid')
            ->orderBy('ua.id', 'DESC')
            ->setParameter('uuid', $userUuid->toRfc4122())
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByUserAndClient(Adherent $user, Client $client): ?UserAuthorization
    {
        return $this->findOneBy(['user' => $user, 'client' => $client]);
    }

    public function findByUuid(Uuid $uuid): ?UserAuthorization
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    public function save(UserAuthorization $userAuthorization): void
    {
        $this->getEntityManager()->persist($userAuthorization);
        $this->getEntityManager()->flush();
    }

    public function delete(UserAuthorization $userAuthorization): void
    {
        $this->getEntityManager()->remove($userAuthorization);
        $this->getEntityManager()->flush();
    }
}
