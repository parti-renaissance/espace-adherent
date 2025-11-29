<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Adherent;

use App\AdherentProfile\AdherentProfile;
use App\AdherentProfile\AdherentProfileHandler;
use App\Entity\Adherent;
use App\Form\AdherentProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/parametres/mon-compte', name: 'app_renaissance_adherent_profile', methods: ['GET', 'POST'])]
class ProfileController extends AbstractController
{
    public function __invoke(Request $request, AdherentProfileHandler $handler): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        $adherentProfile = AdherentProfile::createFromAdherent($adherent);
        $form = $this
            ->createForm(AdherentProfileType::class, $adherentProfile, [
                'disabled_form' => $adherent->isCertified(),
                'is_renaissance' => true,
            ])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $handler->update($adherent, $adherentProfile);
            $this->addFlash('info', 'adherent.update_profile.success');

            return $this->redirectToRoute('app_renaissance_adherent_profile');
        }

        return $this->render('renaissance/adherent/profile/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
