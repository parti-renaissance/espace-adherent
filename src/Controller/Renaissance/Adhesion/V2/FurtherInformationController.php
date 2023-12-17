<?php

namespace App\Controller\Renaissance\Adhesion\V2;

use App\Adhesion\AdhesionStepEnum;
use App\Entity\Adherent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_ADHERENT')]
#[Route(path: '/v2/adhesion/informations-complementaires', name: 'app_adhesion_further_information', methods: ['GET', 'POST'])]
class FurtherInformationController extends AbstractController
{
    public function __invoke(Request $request): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if ($adherent->hasFinishedAdhesionStep(AdhesionStepEnum::FURTHER_INFORMATION)) {
            return $this->redirectToRoute('app_adhesion_further_information');
        }

        return $this->render('renaissance/adhesion/further_information.html.twig');
    }
}
