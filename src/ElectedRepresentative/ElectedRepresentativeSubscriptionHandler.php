<?php

namespace App\ElectedRepresentative;

use App\Entity\ElectedRepresentative\ElectedRepresentative;
use Doctrine\ORM\EntityManagerInterface;

class ElectedRepresentativeSubscriptionHandler
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function unsubscribe(ElectedRepresentative $electedRepresentative): void
    {
        $electedRepresentative->unsubscribeEmails();

        $this->entityManager->flush();
    }
}
