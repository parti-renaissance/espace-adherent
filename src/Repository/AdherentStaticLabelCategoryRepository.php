<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AdherentStaticLabelCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AdherentStaticLabelCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentStaticLabelCategory::class);
    }

    public function findIndexedCodes(): array
    {
        return array_column(
            $this->createQueryBuilder('c')
                ->getQuery()
                ->getArrayResult(),
            'label',
            'code'
        );
    }
}
