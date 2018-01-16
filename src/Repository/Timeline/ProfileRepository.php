<?php

namespace AppBundle\Repository\Timeline;

use AppBundle\Entity\Timeline\Profile;
use AppBundle\Repository\TranslatableRepositoryTrait;
use Doctrine\ORM\EntityRepository;

class ProfileRepository extends EntityRepository
{
    use TranslatableRepositoryTrait;

    public function findOneByTitle(string $title): ?Profile
    {
        $qb = $this
            ->createQueryBuilder('profile')
            ->join('profile.translations', 'translations')
        ;

        return $qb
            ->andWhere('translations.title = :title')
            ->setParameter('title', $title)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
