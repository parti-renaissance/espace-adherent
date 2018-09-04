<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ReferentTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ReferentTagRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ReferentTag::class);
    }

    public function findOneByCode(string $code): ?ReferentTag
    {
        return $this->findOneBy(['code' => $code]);
    }

    public function findByCodes(array $codes): array
    {
        return $this->findBy(['code' => $codes]);
    }

    public function findByPartialName(string $name, int $limit, int $offset): array
    {
        return $this->createQueryBuilder('tag')
            ->where('tag.name LIKE :name')
            ->setParameter('name', $name.'%')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
        ;
    }
}
