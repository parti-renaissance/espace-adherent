<?php

namespace AppBundle\Repository\Timeline;

use AppBundle\Entity\Timeline\Manifesto;
use AppBundle\Repository\TranslatableRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ManifestoRepository extends ServiceEntityRepository
{
    use TranslatableRepositoryTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Manifesto::class);
    }

    public function findOneByTitle(string $title): ?Manifesto
    {
        return $this
            ->createQueryBuilder('manifesto')
            ->join('manifesto.translations', 'translations')
            ->andWhere('translations.title = :title')
            ->setParameter('title', $title)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
