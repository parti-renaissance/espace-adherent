<?php

namespace AppBundle\Repository;

use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Entity\Donation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class DonationRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Donation::class);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByUuid(string $uuid): ?Donation
    {
        return $this->findOneByValidUuid($uuid);
    }

    public function findByEmailAddressOrderedByDonatedAt(string $email): array
    {
        return $this->findBy(
            ['emailAddress' => $email],
            ['donatedAt' => 'DESC']
        );
    }

    /**
     * @return Donation[]
     */
    public function findAllSubscribedDonationByEmail(string $email): array
    {
        return $this->createQueryBuilder('donation')
            ->andWhere('donation.emailAddress = :email')
            ->andWhere('donation.duration != :duration')
            ->andWhere('donation.subscriptionEndedAt IS NULL')
            ->andWhere('donation.donatedAt IS NOT NULL')
            ->setParameters([
                'email' => $email,
                'duration' => PayboxPaymentSubscription::NONE,
            ])
            ->orderBy('donation.donatedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findLastSubscriptionEndedDonationByEmail(string $email): ?Donation
    {
        return $this->createQueryBuilder('donation')
            ->andWhere('donation.emailAddress = :email')
            ->andWhere('donation.subscriptionEndedAt IS NOT NULL')
            ->setParameter('email', $email)
            ->orderBy('donation.subscriptionEndedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
