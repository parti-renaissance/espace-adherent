<?php

namespace AppBundle\Controller;

use AppBundle\Controller\Traits\CanaryControllerTrait;
use AppBundle\Entity\TonMacronFriendInvitation;
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
     * @Route("/pourquoichoisirmacron")
     * @Route("/pourquoi-choisir-macron")
     * @Route("/pourquoi-voter-macron")
     * @Method("GET")
     */
    public function redirectAction(): Response
    {
        $this->disableInProduction();

        return $this->redirectToRoute('app_ton_macron_invite');
    }

    /**
     * @Route("/pourquoivotermacron", name="app_ton_macron_invite")
     * @Method("GET|POST")
     */
    public function inviteAction(Request $request): Response
    {
        $this->disableInProduction();

        $session = $request->getSession();
        $handler = $this->get('app.ton_macron.invitation_processor_handler');
        $invitation = $handler->start($session);
        $transition = $handler->getCurrentTransition($invitation);

        $form = $this->createForm(TonMacronInvitationType::class, $invitation, ['transition' => $transition]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($invitationLog = $this->get('app.ton_macron.invitation_processor_handler')->process($session, $invitation)) {
                return $this->redirectToRoute('app_ton_macron_invite_sent', [
                    'uuid' => $invitationLog->getUuid()->toString(),
                ]);
            }

            return $this->redirectToRoute('app_ton_macron_invite');
        }

        return $this->render('ton_macron/invite.html.twig', [
            'invitation' => $invitation,
            'invitation_form' => $form->createView(),
            'transition' => $transition,
        ]);
    }

    /**
     * @Route("/pourquoivotermacron/recommencer", name="app_ton_macron_invite_restart")
     * @Method("GET")
     */
    public function restartInviteAction(Request $request): Response
    {
        $this->disableInProduction();

        $this->get('app.ton_macron.invitation_processor_handler')->terminate($request->getSession());

        return $this->redirectToRoute('app_ton_macron_invite');
    }

    /**
     * @Route("/pourquoivotermacron/{uuid}/merci", name="app_ton_macron_invite_sent")
     * @Method("GET")
     */
    public function inviteSentAction(TonMacronFriendInvitation $invitation): Response
    {
        $this->disableInProduction();

        return $this->render('ton_macron/invite_sent.html.twig', [
            'invitation' => $invitation,
        ]);
    }
}
