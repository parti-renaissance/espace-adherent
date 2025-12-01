<?php

declare(strict_types=1);

namespace App\Repository;

use App\Donation\Paybox\PayboxPaymentSubscription;
use App\Entity\Adherent;
use App\Entity\Donation;
use App\Entity\Donator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Donation>
 */
class DonationRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Donation::class);
    }

    public function findOneByUuid(UuidInterface|string $uuid): ?Donation
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
            ->setParameters(new ArrayCollection([new Parameter('status', Donation::STATUS_SUBSCRIPTION_IN_PROGRESS), new Parameter('email', $email), new Parameter('duration', PayboxPaymentSubscription::NONE)]))
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

    public function getDonationYearsForAdherent(Adherent $adherent): array
    {
        return $this->createQueryBuilder('donation')
            ->select('DISTINCT(YEAR(donation.donatedAt)) AS year')
            ->innerJoin('donation.donator', 'donator')
            ->where('donator.adherent = :adherent')
            ->andWhere('donation.membership = 0 AND donation.status = :donation_status')
            ->setParameters(new ArrayCollection([new Parameter('adherent', $adherent), new Parameter('donation_status', Donation::STATUS_FINISHED)]))
            ->orderBy('donation.donatedAt')
            ->getQuery()
            ->getSingleColumnResult()
        ;
    }

    /** @return Donation[] */
    public function findOfflineDonationsByAdherent(Adherent $adherent): array
    {
        return $this->createQueryBuilder('donation')
            ->innerJoin('donation.donator', 'donator')
            ->leftJoin('donator.adherent', 'adherent')
            ->andWhere('donator.emailAddress = :email OR adherent = :adherent')
            ->andWhere('donation.type != :donation_type_cb')
            ->setParameter('email', $adherent->getEmailAddress())
            ->setParameter('adherent', $adherent)
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

    public function countCotisationByYearForAdherent(Adherent $adherent): array
    {
        return array_column($this->createQueryBuilder('donation')
            ->select('YEAR(donation.donatedAt) AS year')
            ->addSelect('COUNT(DISTINCT(donation.id)) AS total')
            ->innerJoin('donation.donator', 'donator')
            ->where('donator.adherent = :adherent')
            ->andWhere('donation.membership = 1')
            ->andWhere('donation.status = :status')
            ->setParameters(new ArrayCollection([new Parameter('adherent', $adherent), new Parameter('status', Donation::STATUS_FINISHED)]))
            ->groupBy('year')
            ->orderBy('year', 'DESC')
            ->getQuery()
            ->getResult(), 'total', 'year'
        );
    }

    public function countCotisationForAdherent(Adherent $adherent, \DateTime $before): int
    {
        return (int) $this->createQueryBuilder('donation')
            ->select('COUNT(DISTINCT donation.id)')
            ->innerJoin('donation.donator', 'donator')
            ->where('donator.adherent = :adherent')
            ->andWhere('donation.membership = 1')
            ->andWhere('donation.status = :status')
            ->andWhere('donation.donatedAt < :before')
            ->setParameters(new ArrayCollection([new Parameter('adherent', $adherent), new Parameter('status', Donation::STATUS_FINISHED), new Parameter('before', $before)]))
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
