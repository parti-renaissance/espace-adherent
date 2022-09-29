<?php

namespace App\Repository;

use App\Entity\NewsletterSubscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NewsletterSubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsletterSubscription::class);
    }

    public function findById(int $id): ?NewsletterSubscription
    {
        return $this->disableSoftDeleteableFilter()->find($id);
    }

    public function isSubscribed(string $email): bool
    {
        return (bool) $this
            ->createQueryBuilder('newsletter')
            ->select('COUNT(newsletter)')
            ->where('newsletter.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findOneByEmail(string $email): ?NewsletterSubscription
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findOneNotConfirmedByEmail(string $email): ?NewsletterSubscription
    {
        return $this
            ->createQueryBuilder('newsletter')
            ->where('newsletter.email = :email')
            ->andWhere('newsletter.confirmedAt IS NULL')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneNotConfirmedByUuidAndToken(string $uuid, string $token): ?NewsletterSubscription
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

    public function disableSoftDeleteableFilter(): self
    {
        if ($this->_em->getFilters()->has('softdeleteable') && $this->_em->getFilters()->isEnabled('softdeleteable')) {
            $this->_em->getFilters()->disable('softdeleteable');
        }

        return $this;
    }

    public function enableSoftDeleteableFilter(): self
    {
        if ($this->_em->getFilters()->has('softdeleteable')) {
            $this->_em->getFilters()->enable('softdeleteable');
        }

        return $this;
    }
}
