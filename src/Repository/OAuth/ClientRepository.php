<?php

namespace App\Repository\OAuth;

use App\AppCodeEnum;
use App\Entity\OAuth\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function findClientByCredentials(string $identifier, string $secret = ''): ?Client
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->where('c.uuid = :identifier')
            ->setParameter('identifier', $identifier)
        ;

        if ($secret) {
            $qb->andWhere('c.secret = :secret')->setParameter('secret', $secret);
        }

        $this->addActiveClientCriteria($qb);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findClientByUuid(UuidInterface $uuid): ?Client
    {
        $qb = $this->createQueryBuilder('c');

        $this->addActiveClientCriteria($qb);

        return $qb
            ->andWhere('c.uuid = :identifier')
            ->setParameter('identifier', $uuid->toString())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    private function addActiveClientCriteria(QueryBuilder $qb, string $rootAlias = 'c'): void
    {
        $qb->andWhere(\sprintf('%s.deletedAt IS NULL', $rootAlias));
    }

    public function getCadreClient(): Client
    {
        return $this->findOneBy(['code' => AppCodeEnum::JEMENGAGE_WEB]);
    }

    public function getVoxClient(): Client
    {
        return $this->findOneBy(['code' => AppCodeEnum::BESOIN_D_EUROPE]);
    }
}
