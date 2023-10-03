<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Form\Renaissance\Adhesion\PersonalInfoType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/adhesion', name: 'app_renaissance_adhesion', methods: ['GET|POST'])]
class AdhesionController extends AbstractAdhesionController
{
    public function __invoke(Request $request): Response
    {
        $command = $this->getCommand($request);
        $command->setRecaptcha($request->request->get('frc-captcha-solution'));

        if (!$this->processor->canFillPersonalInfo($command)) {
            return $this->redirectToRoute('app_renaissance_homepage');
        }

        $this->processor->doFillPersonalInfo($command);

        $form = $this
            ->createForm(PersonalInfoType::class, $command, [
                'from_adherent' => (bool) $command->getAdherentId(),
                'disable_duplicate' => $command->emailFromRequest,
                'from_certified_adherent' => $command->isCertified(),
            ])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('app_renaissance_adhesion_amount');
        }

        return $this->render('renaissance/adhesion/fill_personal_info.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
