<?php

namespace App\Controller\Renaissance\Adhesion\V2;

use App\Adhesion\AdhesionStepEnum;
use App\Entity\Adherent;
use App\Form\AdherentResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_ADHERENT')]
#[Route(path: '/v2/adhesion/creation-mot-de-passe', name: 'app_adhesion_password_create', methods: ['GET', 'POST'])]
class CreatePasswordController extends AbstractController
{
    public function __invoke(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if ($adherent->hasFinishedAdhesionStep(AdhesionStepEnum::PASSWORD)) {
            return $this->redirectToRoute('app_adhesion_further_information');
        }

        $form = $this
            ->createForm(AdherentResetPasswordType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $adherent->changePassword($passwordHasher->hashPassword($adherent, $form->getData()['password']));
            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a bien été sauvegardé !');

            return $this->redirectToRoute('app_adhesion_further_information');
        }

        return $this->renderForm('renaissance/adhesion/create_password.html.twig', [
            'form' => $form,
        ]);
    }
}
