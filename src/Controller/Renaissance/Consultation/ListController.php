<?php

namespace App\Controller\Renaissance\Consultation;

use App\Repository\ConsultationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[IsGranted('RENAISSANCE_ADHERENT')]
#[Route(path: '/espace-adherent/consultations', name: 'app_renaissance_consultation_list', methods: ['GET'])]
class ListController extends AbstractController
{
    public function __invoke(ConsultationRepository $consultationRepository): Response
    {
        return $this->render('renaissance/consultation/list.html.twig', [
            'consultations' => $consultationRepository->findBy(['published' => true], ['createdAt' => 'DESC']),
        ]);
    }
}
