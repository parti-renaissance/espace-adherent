<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Entity\Adherent;
use App\Form\Renaissance\Adhesion\PersonalInfoType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/v1/adhesion', name: 'app_renaissance_adhesion', methods: ['GET|POST'])]
class AdhesionController extends AbstractAdhesionController
{
    public function __invoke(Request $request): Response
    {
        /** @var Adherent $user */
        if (($user = $this->getUser()) && $user->isRenaissanceUser()) {
            return $this->redirectToRoute('app_renaissance_adherent_space');
        }

        $command = $this->getCommand($request);
        $command->setRecaptcha($request->request->get('frc-captcha-solution'));

        if (!$this->processor->canFillPersonalInfo($command)) {
            return $this->redirectToRoute('renaissance_site');
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
