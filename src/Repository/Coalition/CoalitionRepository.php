<?php

namespace App\Repository\Coalition;

use App\Entity\Coalition\Cause;
use App\Entity\Coalition\Coalition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
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
            ->where('coalition.enabled = :true')
            ->setParameters([
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
                ->join('coalition.causes', 'cause', Join::WITH, 'cause.status = :approved')
                ->join('cause.followers', 'follower')
                ->andWhere('follower.emailAddress = :email')
                ->setParameter('approved', Cause::STATUS_APPROVED)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function findEnabled(): QueryBuilder
    {
        return $this->createQueryBuilder('coalition')
            ->where('coalition.enabled = :true')
            ->setParameter('true', true)
        ;
    }
}
