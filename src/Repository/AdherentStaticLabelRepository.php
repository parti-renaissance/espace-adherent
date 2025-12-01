<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AdherentStaticLabel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\AdherentStaticLabel>
 */
class AdherentStaticLabelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentStaticLabel::class);
    }

    public function findIndexedCodes(): array
    {
        return array_column(
            $this->createQueryBuilder('label')
                ->getQuery()
                ->getArrayResult(),
            'label',
            'code'
        );
    }

    public function findAllLikeAdherentTags(): array
    {
        return $this->createQueryBuilder('label')
            ->addSelect('category')
            ->innerJoin('label.category', 'category')
            ->where('category.sync = :sync')
            ->setParameter('sync', true)
            ->getQuery()
            ->getResult()
        ;
    }
}
