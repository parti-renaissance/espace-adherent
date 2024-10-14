<?php

namespace App\Repository;

use App\Entity\AdherentStaticLabel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AdherentStaticLabelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentStaticLabel::class);
    }

    public function findIndexedCodes(): array
    {
        $qb = $this->createQueryBuilder('label');

        $query = $qb
            ->select('label.code, label.label')
            ->getQuery()
        ;

        $labels = [];
        foreach ($query->getArrayResult() as $label) {
            $labels[$label['code']] = $label['label'];
        }

        return $labels;
    }
}
