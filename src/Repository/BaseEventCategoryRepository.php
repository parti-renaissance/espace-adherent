<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Event\BaseEventCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class BaseEventCategoryRepository extends ServiceEntityRepository
{
    public function findAllEnabledOrderedByName(): array
    {
        return $this
            ->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', BaseEventCategory::ENABLED)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
