<?php

declare(strict_types=1);

namespace App\Repository\Renaissance;

use App\Entity\Renaissance\NewsletterSubscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Renaissance\NewsletterSubscription>
 */
class NewsletterSubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsletterSubscription::class);
    }

    public function findById(int $id): ?NewsletterSubscription
    {
        return $this->find($id);
    }

    public function findOneByEmail(string $email): ?NewsletterSubscription
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findOneByUuidAndToken(UuidInterface|string $uuid, string $token): ?NewsletterSubscription
    {
        return $this->findOneBy(['uuid' => $uuid, 'token' => $token]);
    }

    public function createQueryBuilderForSynchronization(): QueryBuilder
    {
        return $this->createQueryBuilder('newsletter');
    }
}
