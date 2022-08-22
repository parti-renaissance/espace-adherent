<?php

namespace App\Repository;

use App\Donation\DonationSourceEnum;
use App\Donation\PayboxPaymentSubscription;
use App\Entity\Adherent;
use App\Entity\Donation;
use App\Entity\Donator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class DonationRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    public function __construct(ManagerRegistry $registry)
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

    public function findOfflineDonationsByEmail(string $email): array
    {
        return $this->createQueryBuilder('donation')
            ->innerJoin('donation.donator', 'donator')
            ->andWhere('donator.emailAddress = :email')
            ->andWhere('donation.type != :donation_type_cb')
            ->setParameter('email', $email)
            ->setParameter('donation_type_cb', Donation::TYPE_CB)
            ->orderBy('donation.donatedAt', 'DESC')
            ->getQuery()
            ->getResult()
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

    public function findInProgressMembershipDonationFromAdherent(Adherent $adherent): ?Donation
    {
        return $this->createQueryBuilder('donation')
            ->innerJoin('donation.donator', 'donator')
            ->leftJoin('donator.adherent', 'adherent')
            ->andWhere('adherent = :adherent')
            ->andWhere('donation.status = :status')
            ->andWhere('donation.source = :source')
            ->setParameter('adherent', $adherent)
            ->setParameter('status', Donation::STATUS_WAITING_CONFIRMATION)
            ->setParameter('source', DonationSourceEnum::MEMBERSHIP)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
