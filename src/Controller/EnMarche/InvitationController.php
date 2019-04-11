<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\Invite;
use AppBundle\Form\InvitationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class InvitationController extends Controller
{
    /**
     * @Route("/invitation", name="invitation_form", methods={"GET", "POST"})
     */
    public function invitationAction(Request $request)
    {
        $invite = Invite::createWithCaptcha((string) $request->request->get('g-recaptcha-response'));

        $form = $this->createForm(InvitationType::class, $invite);
        $form->add('submit', SubmitType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.invitation_request_handler')->handle($invite, $request);

            return $this->render('invitation/sent.html.twig', [
                'invite' => $invite,
            ]);
        }

        return $this->render('invitation/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
