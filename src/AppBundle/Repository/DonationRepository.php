<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Donation;
use Doctrine\ORM\EntityRepository;

class DonationRepository extends EntityRepository
{
    public function findOneByUuid(string $uuid): ?Donation
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }
}
