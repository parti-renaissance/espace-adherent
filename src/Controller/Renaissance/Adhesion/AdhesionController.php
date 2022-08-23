<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Form\Renaissance\Adhesion\MembershipRequestPersonalInfoType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/adhesion", name="app_renaissance_adhesion", methods={"GET|POST"})
 */
class AdhesionController extends AbstractAdhesionController
{
    public function __invoke(Request $request): Response
    {
        $command = $this->getCommand();

        if (!$this->processor->canFillPersonalInfo($command)) {
            return $this->redirectToRoute('app_ren_homepage');
        }

        $this->processor->doFillPersonalInfo($command);

        $form = $this
            ->createForm(MembershipRequestPersonalInfoType::class, $command)
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
