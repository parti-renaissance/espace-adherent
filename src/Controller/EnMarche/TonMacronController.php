<?php

namespace App\Controller\EnMarche;

use App\Entity\TonMacronFriendInvitation;
use App\Form\TonMacronInvitationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TonMacronController extends Controller
{
    /**
     * @Route("/pourquoi-voter-macron", methods={"GET"})
     */
    public function redirectAction(): Response
    {
        return $this->redirectToRoute('app_ton_macron_invite');
    }

    /**
     * @Route("/pourquoi-voter-le-candidat-la-republique-en-marche", methods={"GET"})
     */
    public function redirectLegislativesAction(): Response
    {
        return $this->redirectToRoute('app_ton_macron_invite');
    }

    /**
     * @Route("/pourquoivoterenmarche", name="app_ton_macron_invite", methods={"GET", "POST"})
     */
    public function inviteAction(Request $request): Response
    {
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
     * @Route("/pourquoivoterenmarche/recommencer", name="app_ton_macron_invite_restart", methods={"GET"})
     */
    public function restartInviteAction(Request $request): Response
    {
        $this->get('app.ton_macron.invitation_processor_handler')->terminate($request->getSession());

        return $this->redirectToRoute('app_ton_macron_invite');
    }

    /**
     * @Route("/pourquoivoterenmarche/{uuid}/merci", name="app_ton_macron_invite_sent", methods={"GET"})
     */
    public function inviteSentAction(TonMacronFriendInvitation $invitation): Response
    {
        return $this->render('ton_macron/invite_sent.html.twig', [
            'invitation' => $invitation,
        ]);
    }
}
