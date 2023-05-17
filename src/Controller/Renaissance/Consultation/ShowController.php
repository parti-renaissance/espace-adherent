<?php

namespace App\Controller\Renaissance\Consultation;

use App\Entity\Consultation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/espace-adherent/consultations/{uuid}', name: 'app_renaissance_consultation_show', methods: ['GET'])]
#[Entity('consultation', expr: 'repository.findOnePublished(uuid)')]
#[IsGranted('RENAISSANCE_ADHERENT')]
class ShowController extends AbstractController
{
    public function __invoke(Consultation $consultation): Response
    {
        return $this->render('renaissance/consultation/show.html.twig', [
            'consultation' => $consultation,
        ]);
    }
}
