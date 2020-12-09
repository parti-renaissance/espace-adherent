<?php

namespace App\Repository\Timeline;

use App\Entity\Timeline\Profile;
use App\Repository\TranslatableRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ProfileRepository extends ServiceEntityRepository
{
    use TranslatableRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Profile::class);
    }

    public function findOneByTitle(string $title): ?Profile
    {
        return $this
            ->createQueryBuilder('profile')
            ->join('profile.translations', 'translations')
            ->andWhere('translations.title = :title')
            ->setParameter('title', $title)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
