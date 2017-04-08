<?php

namespace AppBundle\Controller;

use AppBundle\Controller\Traits\CanaryControllerTrait;
use AppBundle\Form\TonMacronInvitationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TonMacronController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route("/pourquoi-choisir-macron")
     * @Method("GET")
     */
    public function redirectAction(): Response
    {
        if (!((bool) $this->getParameter('enable_canary'))) {
            throw $this->createNotFoundException();
        }

        return $this->redirectToRoute('app_ton_macron_invite');
    }

    /**
     * @Route("/pourquoichoisirmacron", name="app_ton_macron_invite")
     * @Method("GET|POST")
     */
    public function inviteAction(Request $request): Response
    {
        $this->enableCanary();

        $session = $request->getSession();
        $handler = $this->get('app.ton_macron.invitation_processor_handler');
        $invitation = $handler->start($session);
        $transition = $handler->getCurrentTransition($invitation);

        $form = $this->createForm(TonMacronInvitationType::class, $invitation, ['transition' => $transition]);
        $form->handleRequest($request);

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
    public function restartInviteAction(Request $request): Response
    {
        $this->enableCanary();

        $this->get('app.ton_macron.invitation_processor_handler')->terminate($request->getSession());

        return $this->redirectToRoute('app_ton_macron_invite');
    }

    /**
     * @Route("/merci", name="app_ton_macron_invite_sent")
     * @Method("GET")
     */
    public function inviteSentAction(): Response
    {
        $this->enableCanary();

        return $this->render('ton_macron/invite_sent.html.twig');
    }
}
