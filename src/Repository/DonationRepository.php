<?php

namespace AppBundle\Repository;

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
}
