<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\NewsletterSubscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

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

    public function createQueryBuilderForSynchronization(): QueryBuilder
    {
        return $this->createQueryBuilder('newsletter');
    }
}
