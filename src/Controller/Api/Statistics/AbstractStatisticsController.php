<?php

namespace App\Controller\Api\Statistics;

use App\Entity\Adherent;
use App\Repository\AdherentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractStatisticsController extends AbstractController
{
    public function __construct(protected readonly AdherentRepository $adherentRepository)
    {
    }

    protected function findReferent(Request $request): Adherent
    {
        if (
            !($referentIdentifier = $request->query->get('referent'))
            || !($referent = $this->adherentRepository->findReferent($referentIdentifier))
        ) {
            throw $this->createNotFoundException();
        }

        return $referent;
    }
}
