<?php

namespace AppBundle\Controller;

use AppBundle\Form\TonMacronInvitationType;
use AppBundle\TonMacron\InvitationProcessor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Workflow\StateMachine;

class TonMacronController extends Controller
{
    /**
     * @Route("/ton-macron/invitation", name="app_ton_macron_invite")
     * @Method("GET|POST")
     */
    public function inviteAction(Request $request): Response
    {
        $session = $request->getSession();
        $invitation = $session->get('ton_macron.invitation', new InvitationProcessor()); // TODO move this line in a handler

        /** @var StateMachine $stateMachine */
        $stateMachine = $this->get('state_machine.ton_macron_invitation');
        $transition = current($stateMachine->getEnabledTransitions($invitation))->getName();
        $form = $this->createForm(TonMacronInvitationType::class, $invitation, [
            'transition' => $transition,
        ])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($stateMachine->can($invitation, InvitationProcessor::TRANSITION_SEND)) {
                // TODO handle invite
                $stateMachine->apply($invitation, $transition);
                $session->remove('ton_macron.invitation');

                return $this->redirectToRoute('homepage');
            }

            $stateMachine->apply($invitation, $transition);
            $session->set('ton_macron.invitation', $invitation);

            return $this->redirectToRoute('app_ton_macron_invite');
        }

        return $this->render('ton_macron/invite.html.twig', [
            'invitation_form' => $form->createView(),
            'transition' => $transition,
        ]);
    }
}
