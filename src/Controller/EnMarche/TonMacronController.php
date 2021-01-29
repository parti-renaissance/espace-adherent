<?php

namespace App\Controller\EnMarche;

use App\Entity\TonMacronFriendInvitation;
use App\Form\TonMacronInvitationType;
use App\TonMacron\InvitationProcessorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TonMacronController extends AbstractController
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
    public function inviteAction(Request $request, InvitationProcessorHandler $handler): Response
    {
        $session = $request->getSession();
        $invitation = $handler->start($session);
        $transition = $handler->getCurrentTransition($invitation);

        $form = $this
            ->createForm(TonMacronInvitationType::class, $invitation, ['transition' => $transition])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($invitationLog = $handler->process($session, $invitation)) {
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
    public function restartInviteAction(Request $request, InvitationProcessorHandler $handler): Response
    {
        $handler->terminate($request->getSession());

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
