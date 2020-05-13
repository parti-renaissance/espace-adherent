<?php

namespace App\Repository;

use App\Entity\Mooc\Mooc;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MoocRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Mooc::class);
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('mooc')
            ->addSelect('chapters', 'elements')
            ->leftJoin('mooc.chapters', 'chapters')
            ->leftJoin('chapters.elements', 'elements')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllOrdered(): array
    {
        return $this->findBy([], ['createdAt' => 'ASC']);
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
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
