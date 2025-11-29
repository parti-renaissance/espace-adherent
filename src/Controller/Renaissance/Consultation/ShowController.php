<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Consultation;

use App\Entity\Consultation;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('RENAISSANCE_ADHERENT')]
#[Route(path: '/espace-adherent/consultations/{uuid}', name: 'app_renaissance_consultation_show', methods: ['GET'])]
class ShowController extends AbstractController
{
    public function __invoke(
        #[MapEntity(expr: 'repository.findOnePublished(uuid)')]
        Consultation $consultation,
    ): Response {
        return $this->render('renaissance/consultation/show.html.twig', [
            'consultation' => $consultation,
        ]);
    }
}
