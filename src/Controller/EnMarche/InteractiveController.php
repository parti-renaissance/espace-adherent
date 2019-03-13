<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\MyEuropeInvitation;
use AppBundle\Form\MyEuropeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InteractiveController extends Controller
{
    /**
     * @Route("/mon-europe", name="app_my_europe")
     * @Method("GET|POST")
     */
    public function myEuropeAction(Request $request): Response
    {
        $session = $request->getSession();
        $handler = $this->get('app.interactive.my_europe_processor_handler');
        $myEurope = $handler->start($session, (string) $request->request->get('g-recaptcha-response'));
        $transition = $handler->getCurrentTransition($myEurope);

        $form = $this
            ->createForm(MyEuropeType::class, $myEurope, ['transition' => $transition])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($myEuropeLog = $this->get('app.interactive.my_europe_processor_handler')->process($session, $myEurope)) {
                return $this->redirectToRoute('app_my_europe_mail_sent', [
                    'uuid' => $myEuropeLog->getUuid()->toString(),
                ]);
            }

            return $this->redirectToRoute('app_my_europe');
        }

        return $this->render('interactive/my_europe.html.twig', [
            'interactive' => $myEurope,
            'interactive_form' => $form->createView(),
            'transition' => $transition,
        ]);
    }

    /**
     * @Route("/mon-europe/recommencer", name="app_my_europe_restart")
     * @Method("GET")
     */
    public function restartMyEuropeAction(Request $request): Response
    {
        $this->get('app.interactive.my_europe_processor_handler')->terminate($request->getSession());

        return $this->redirectToRoute('app_my_europe');
    }

    /**
     * @Route("/mon-europe/{uuid}/merci", name="app_my_europe_mail_sent")
     * @Method("GET")
     */
    public function mailSentAction(MyEuropeInvitation $myEurope): Response
    {
        return $this->render('interactive/mail_sent.html.twig', [
            'interactive' => $myEurope,
        ]);
    }
}
