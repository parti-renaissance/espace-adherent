<?php

namespace App\Repository\Renaissance;

use App\Entity\Renaissance\NewsletterSubscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NewsletterSubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsletterSubscription::class);
    }

    public function findOneByEmail(string $email): ?NewsletterSubscription
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findOneByUuidAndToken(string $uuid, string $token): ?NewsletterSubscription
    {
        return $this->findOneBy(['uuid' => $uuid, 'token' => $token]);
    }
}
