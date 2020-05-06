<?php

namespace App\Repository;

use App\Donation\PayboxPaymentSubscription;
use App\Entity\Donation;
use App\Entity\Donator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DonationRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Donation::class);
    }

    public function findOneByUuid(string $uuid): ?Donation
    {
        return $this->findOneByValidUuid($uuid);
    }

    /**
     * @return Donation[]
     */
    public function findAllSubscribedDonationByEmail(string $email): array
    {
        return $this->createQueryBuilder('donation')
            ->innerJoin('donation.donator', 'donator')
            ->andWhere('donator.emailAddress = :email')
            ->andWhere('donation.duration != :duration')
            ->andWhere('donation.status = :status')
            ->andWhere('donation.subscriptionEndedAt IS NULL')
            ->setParameters([
                'status' => Donation::STATUS_SUBSCRIPTION_IN_PROGRESS,
                'email' => $email,
                'duration' => PayboxPaymentSubscription::NONE,
            ])
            ->orderBy('donation.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findLastSubscriptionEndedDonationByEmail(string $email): ?Donation
    {
        return $this->createQueryBuilder('donation')
            ->innerJoin('donation.donator', 'donator')
            ->andWhere('donator.emailAddress = :email')
            ->andWhere('donation.subscriptionEndedAt IS NOT NULL')
            ->setParameter('email', $email)
            ->orderBy('donation.subscriptionEndedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getSubscriptionsForDonatorQueryBuilder(Donator $donator): QueryBuilder
    {
        return $this
            ->createQueryBuilder('donation')
            ->andWhere('donation.donator = :donator')
            ->addOrderBy('donation.donatedAt', 'DESC')
            ->setParameter('donator', $donator)
        ;
    }
}
