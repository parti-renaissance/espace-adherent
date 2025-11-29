<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Adhesion;

use App\Adhesion\AdhesionStepEnum;
use App\Entity\Adherent;
use App\Form\AdherentResetPasswordType;
use App\Utils\UtmParams;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/adhesion/creation-mot-de-passe', name: self::ROUTE_NAME, methods: ['GET', 'POST'])]
class CreatePasswordController extends AbstractController
{
    public const ROUTE_NAME = 'app_adhesion_password_create';

    public function __invoke(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $utmParams = UtmParams::fromRequest($request);

        $adherent = $this->getUser();
        if (!$adherent instanceof Adherent) {
            return $this->redirectToRoute(AdhesionController::ROUTE_NAME, $utmParams);
        }

        if ($adherent->hasFinishedAdhesionStep(AdhesionStepEnum::PASSWORD)) {
            return $this->redirectToRoute('vox_app_redirect');
        }

        $form = $this
            ->createForm(AdherentResetPasswordType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $adherent->changePassword($passwordHasher->hashPassword($adherent, $form->getData()['password']));
            $adherent->finishAdhesionStep(AdhesionStepEnum::PASSWORD);

            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a bien été sauvegardé !');

            return $this->redirectToRoute(FurtherInformationController::ROUTE_NAME, $utmParams);
        }

        return $this->render('renaissance/adhesion/create_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
