<?php

namespace App\Repository;

use App\Entity\SubscriptionType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SubscriptionTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubscriptionType::class);
    }

    /**
     * @param string[]
     *
     * @return SubscriptionType[]
     */
    public function findByCodes(array $codes): array
    {
        return $this->findBy(['code' => $codes]);
    }

    public function findOneByCode(string $code): ?SubscriptionType
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * Finds an SubscriptionType instance by external id.
     */
    public function findOneByExternalId(string $externalId): ?SubscriptionType
    {
        return $this->findOneBy(['externalId' => $externalId]);
    }
}
