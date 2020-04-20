<?php

namespace AppBundle\Adherent;

use AppBundle\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface;

class CertificationManager
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function certify(Adherent $adherent): void
    {
        $adherent->certify();

        $this->entityManager->flush();
    }
}
