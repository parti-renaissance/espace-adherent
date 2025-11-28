<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Adhesion;

use App\Adhesion\AdhesionStepEnum;
use App\Entity\Adherent;
use App\Form\AdhesionFurtherInformationType;
use App\Utils\UtmParams;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/adhesion/informations-complementaires', name: self::ROUTE_NAME, methods: ['GET', 'POST'])]
class FurtherInformationController extends AbstractController
{
    public const ROUTE_NAME = 'app_adhesion_further_information';

    public function __invoke(Request $request, EntityManagerInterface $entityManager): Response
    {
        $utmParams = UtmParams::fromRequest($request);

        $adherent = $this->getUser();
        if (!$adherent instanceof Adherent) {
            return $this->redirectToRoute(AdhesionController::ROUTE_NAME, $utmParams);
        }

        if ($adherent->hasFinishedAdhesionStep(AdhesionStepEnum::FURTHER_INFORMATION)) {
            return $this->redirectToRoute('vox_app_redirect');
        }

        $form = $this
            ->createForm(AdhesionFurtherInformationType::class, $adherent, ['validation_groups' => ['adhesion:further_information']])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $adherent->finishAdhesionStep(AdhesionStepEnum::FURTHER_INFORMATION);
            $entityManager->flush();

            return $this->redirectToRoute(MemberCardController::ROUTE_NAME, $utmParams);
        }

        return $this->render('renaissance/adhesion/further_information.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
