<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\Interactive;
use AppBundle\Entity\InteractiveInvitation;
use AppBundle\Form\InteractiveType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InteractiveController extends Controller
{
    /**
     * @Route("/interactif/{slug}", name="app_interactive")
     * @Method("GET|POST")
     */
    public function interactiveAction(Request $request, Interactive $interactive): Response
    {
        $session = $request->getSession();
        $handler = $this->get('app.interactive.interactive_processor_handler');
        $interactiveInvitation = $handler->start($session);
        $transition = $handler->getCurrentTransition($interactiveInvitation);

        $form = $this->createForm(InteractiveType::class, $interactiveInvitation, [
            'transition' => $transition,
            'interactive' => $interactive,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($interactiveInvitationLog = $this->get('app.interactive.interactive_processor_handler')->process($session, $interactiveInvitation)) {
                return $this->redirectToRoute('app_interactive_mail_sent', [
                    'slug' => $interactive->getSlug(),
                    'uuid' => $interactiveInvitationLog->getUuid()->toString(),
                ]);
            }

            return $this->redirectToRoute('app_interactive', [
                'slug' => $interactive->getSlug(),
            ]);
        }

        return $this->render('interactive/interactive.html.twig', [
            'interactive' => $interactiveInvitation,
            'interactive_form' => $form->createView(),
            'transition' => $transition,
            'slug' => $interactive->getSlug(),
        ]);
    }

    /**
     * @Route("/interactif/{slug}/recommencer", name="app_interactive_restart")
     * @Method("GET")
     */
    public function restartInteractiveAction(Interactive $interactive, Request $request): Response
    {
        $this->get('app.interactive.interactive_processor_handler')->terminate($request->getSession());

        return $this->redirectToRoute('app_interactive', [
            'slug' => $interactive->getSlug(),
        ]);
    }

    /**
     * @Route("/interactif/{slug}/{uuid}/merci", name="app_interactive_mail_sent")
     * @ParamConverter("interactive", options={"mapping": {"slug":"slug"}})
     * @ParamConverter("interactiveInvitation", options={"mapping": {"uuid":"uuid"}})
     * @Method("GET")
     */
    public function mailSentAction(Interactive $interactive, InteractiveInvitation $interactiveInvitation, Request $request): Response
    {
        return $this->render('interactive/mail_sent.html.twig', [
            'interactive' => $interactiveInvitation,
            'slug' => $interactive->getSlug(),
        ]);
    }
}
