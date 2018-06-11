<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Mooc\Mooc;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MoocRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Mooc::class);
    }

    public function findOneBySlug(string $slug): ?Mooc
    {
        return $this->createQueryBuilder('mooc')
            ->addSelect('chapters', 'elements', 'attachmentLinks', 'attachmentFiles')
            ->leftJoin('mooc.chapters', 'chapters')
            ->leftJoin('chapters.elements', 'elements')
            ->leftJoin('elements.links', 'attachmentLinks')
            ->leftJoin('elements.files', 'attachmentFiles')
            ->where('mooc.slug = :slug')
            ->andWhere('chapters.published = :published')
            ->setParameter('slug', $slug)
            ->setParameter('published', true)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
