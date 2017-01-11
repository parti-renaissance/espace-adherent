<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Donation;
use Doctrine\Common\Persistence\ManagerRegistry;

class DonationManager
{
    private $manager;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->manager = $doctrine->getManagerForClass(Donation::class);
    }

    public function persist(Donation $donation, string $clientIp)
    {
        $donation->init($clientIp);

        $this->manager->persist($donation);
        $this->manager->flush();
    }
}
