<?php

namespace App\Controller\Api;

use App\Entity\Adherent;
use App\Entity\AdherentFormation\Formation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AbstractFormationContentController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    protected function printFormation(Formation $formation): void
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if (
            !$adherent instanceof Adherent
            && $this->isGranted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN')
        ) {
            return;
        }

        if ($formation->addPrintByAdherent($adherent)) {
            $this->entityManager->flush();
        }
    }
}
