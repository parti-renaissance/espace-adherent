<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\LegislativeNewsletterSubscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\LegislativeNewsletterSubscription>
 */
class LegislativeNewsletterSubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LegislativeNewsletterSubscription::class);
    }

    public function findOneNotConfirmedByUuidAndToken(string $uuid, string $token): ?LegislativeNewsletterSubscription
    {
        return $this
            ->createQueryBuilder('newsletter')
            ->where('newsletter.uuid = :uuid')
            ->andWhere('newsletter.token = :token')
            ->andWhere('newsletter.confirmedAt IS NULL')
            ->setParameter('uuid', $uuid)
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
