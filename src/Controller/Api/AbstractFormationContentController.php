<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Adherent;
use App\Entity\AdherentFormation\Formation;
use App\OAuth\Model\Scope;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractFormationContentController extends AbstractController
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
            || $this->isGranted(Scope::generateRole(Scope::JEMENGAGE_ADMIN))
        ) {
            return;
        }

        if ($formation->addPrintByAdherent($adherent)) {
            $this->entityManager->flush();
        }
    }
}
