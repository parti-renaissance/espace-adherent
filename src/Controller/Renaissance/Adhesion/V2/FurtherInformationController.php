<?php

namespace App\Controller\Renaissance\Adhesion\V2;

use App\Adhesion\AdhesionStepEnum;
use App\Entity\Adherent;
use App\Form\AdhesionFurtherInformationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/adhesion/informations-complementaires', name: 'app_adhesion_further_information', methods: ['GET', 'POST'])]
class FurtherInformationController extends AbstractController
{
    public function __invoke(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var Adherent $adherent */
        if (!($adherent = $this->getUser()) instanceof Adherent) {
            return $this->redirectToRoute('app_adhesion_index');
        }

        if ($adherent->hasFinishedAdhesionStep(AdhesionStepEnum::FURTHER_INFORMATION)) {
            return $this->redirectToRoute('app_renaissance_adherent_space');
        }

        $form = $this
            ->createForm(AdhesionFurtherInformationType::class, $adherent, ['validation_groups' => ['adhesion:further_information']])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $adherent->finishAdhesionStep(AdhesionStepEnum::FURTHER_INFORMATION);
            $entityManager->flush();

            return $this->redirectToRoute('app_adhesion_finish');
        }

        return $this->renderForm('renaissance/adhesion/further_information.html.twig', [
            'form' => $form,
        ]);
    }
}
