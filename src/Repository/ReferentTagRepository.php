<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ReferentTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ReferentTagRepository extends ServiceEntityRepository
{
    public const FRENCH_OUTSIDE_FRANCE_TAG = 'FOF';

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

    public function createSelectSenatorAreaQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('tag')
            ->where('tag.type = :type_dpt OR tag.code = :code')
            ->setParameters([
                'type_dpt' => ReferentTag::TYPE_DEPARTMENT,
                'code' => self::FRENCH_OUTSIDE_FRANCE_TAG,
            ])
        ;
    }
}
