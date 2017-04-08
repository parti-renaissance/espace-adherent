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

/**
 * @Route("/ton-macron/invitation")
 */
class TonMacronController extends Controller
{
    /**
     * @Route(name="app_ton_macron_invite")
     * @Method("GET|POST")
     */
    public function inviteAction(Request $request): Response
    {
        if (!((bool) $this->getParameter('enable_canary'))) {
            throw $this->createNotFoundException();
        }

        $session = $request->getSession();
        $handler = $this->get('app.ton_macron.invitation_processor_handler');
        $invitation = $handler->start($session);
        $transition = $handler->getCurrentTransition($invitation);
        $form = $this->createForm(TonMacronInvitationType::class, $invitation, [
            'transition' => $transition,
        ])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->get('app.ton_macron.invitation_processor_handler')->process($session, $invitation)) {
                return $this->redirectToRoute('app_ton_macron_invite_sent');
            }

            return $this->redirectToRoute('app_ton_macron_invite');
        }

        return $this->render('ton_macron/invite.html.twig', [
            'invitation_form' => $form->createView(),
            'transition' => $transition,
        ]);
    }

    /**
     * @Route("/recommencer", name="app_ton_macron_invite_restart")
     * @Method("GET")
     */
    public function restartInviteAction(Request $request)
    {
        if (!((bool) $this->getParameter('enable_canary'))) {
            throw $this->createNotFoundException();
        }

        $request->getSession()->remove('ton_macron.invitation');

        return $this->redirectToRoute('app_ton_macron_invite');
    }

    /**
     * @Route("/merci", name="app_ton_macron_invite_sent")
     * @Method("GET")
     */
    public function inviteSentAction()
    {
        if (!((bool) $this->getParameter('enable_canary'))) {
            throw $this->createNotFoundException();
        }

        return $this->render('ton_macron/invite_sent.html.twig');
    }
}
