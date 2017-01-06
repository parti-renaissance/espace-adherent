<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\InvitationType;

class InviteController extends Controller
{
    /**
     * @Route("/invitation", name="invitation")
     * @Method({"GET", "POST"})
     */
    public function invitationAction(Request $request)
    {
        $form = $this->createForm(InvitationType::class);
        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $fullName = $data['firstName'].' '.$data['lastName'];
            $this->get('app.mailer')->sendInvitationMail($data['email'], $fullName, $data['message']);

            return $this->redirectToRoute('invitation_confirmation');
        }

        return $this->render('invite/invitation.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/invitation/invitation-reussie", name="invitation_confirmation")
     * @Method({"GET"})
     */
    public function confirmationAction()
    {
        return $this->render('invite/confirmation.html.twig');
    }
}
