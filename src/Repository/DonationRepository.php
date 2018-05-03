<?php

namespace AppBundle\Repository;

use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Entity\Donation;
use Doctrine\ORM\EntityRepository;

class DonationRepository extends EntityRepository
{
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByUuid(string $uuid): ?Donation
    {
        return $this->findOneByValidUuid($uuid);
    }

    public function findByEmailAddressOrderedByDonatedAt(string $email, string $order = 'ASC'): array
    {
        return $this->findBy(
            ['emailAddress' => $email],
            ['donatedAt' => $order]
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
            ->setParameters([
                'email' => $email,
                'duration' => PayboxPaymentSubscription::NONE,
            ])
            ->orderBy('donation.donatedAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
