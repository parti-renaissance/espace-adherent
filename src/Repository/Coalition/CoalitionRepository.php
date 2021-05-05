<?php

namespace App\Repository\Coalition;

use App\Entity\Coalition\Cause;
use App\Entity\Coalition\Coalition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class CoalitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Coalition::class);
    }

    public function findFollowedBy(UserInterface $user): array
    {
        return $this->createQueryBuilder('coalition')
            ->leftJoin('coalition.followers', 'follower')
            ->andWhere('follower.adherent = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByCauseAuthor(string $email): array
    {
        $qb = $this->createQueryBuilder('coalition')
            ->join('coalition.causes', 'cause')
            ->join('cause.author', 'author')
            ->where('cause.status = :approved AND coalition.enabled = :true')
            ->andWhere('author.emailAddress = :email')
            ->setParameters([
                'approved' => Cause::STATUS_APPROVED,
                'true' => true,
                'email' => $email,
            ])
        ;

        return $qb->getQuery()->getResult();
    }

    public function findByFollower(string $email, bool $isAdherent): array
    {
        $qb = $this->createQueryBuilder('coalition')
            ->join('coalition.causes', 'cause')
            ->where('coalition.enabled = :true AND cause.status = :approved')
            ->setParameters([
                'approved' => Cause::STATUS_APPROVED,
                'true' => true,
                'email' => $email,
            ])
        ;

        if ($isAdherent) {
            $qb
                ->join('coalition.followers', 'follower')
                ->join('follower.adherent', 'adherent')
                ->andWhere('adherent.emailAddress = :email')
            ;
        } else {
            $qb
                ->join('cause.followers', 'follower')
                ->andWhere('follower.emailAddress = :email')
            ;
        }

        return $qb->getQuery()->getResult();
    }
}
