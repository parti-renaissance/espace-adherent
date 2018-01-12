<?php

namespace AppBundle\Repository\Timeline;

use AppBundle\Entity\Timeline\Profile;
use Doctrine\ORM\EntityRepository;

class ProfileRepository extends EntityRepository
{
    public function findOneByTitle(string $title): ?Profile
    {
        return $this->createQueryBuilder('profile')
            ->join('profile.translations', 'translations')
            ->where('translations.title = :title')
            ->setParameter('title', $title)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
