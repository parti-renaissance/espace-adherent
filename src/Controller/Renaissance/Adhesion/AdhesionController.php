<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Form\Renaissance\Adhesion\MembershipRequestPersonalInfoType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="app_renaissance_adhesion", methods={"GET|POST"})
 */
class AdhesionController extends AbstractAdhesionController
{
    public function __invoke(Request $request): Response
    {
        $membershipRequestCommand = $this->storage->getMembershipRequestCommand();

        if (!$this->processor->canFillPersonalInfo($membershipRequestCommand)) {
            return $this->redirectToRoute('app_ren_homepage');
        }

        $this->processor->doFillPersonalInfo($membershipRequestCommand);

        $form = $this
            ->createForm(
                MembershipRequestPersonalInfoType::class,
                $membershipRequestCommand
            )
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->processor->canChooseAmount($membershipRequestCommand)) {
                return $this->redirectToRoute('app_renaissance_adhesion');
            }

            $this->processor->doChooseAmount($membershipRequestCommand);

            return $this->redirectToRoute('app_renaissance_adhesion_amount');
        }

        return $this->render('renaissance/adhesion/fill_personal_info.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
