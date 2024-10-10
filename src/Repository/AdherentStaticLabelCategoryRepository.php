<?php

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
}
