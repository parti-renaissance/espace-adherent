<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\PurchasingPowerInvitation;
use AppBundle\Form\PurchasingPowerType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InteractiveController extends Controller
{
    /**
     * @Route("/ton-pouvoir-achat", name="app_purchasing_power")
     * @Method("GET|POST")
     */
    public function purchasingPowerAction(Request $request): Response
    {
        $session = $request->getSession();
        $handler = $this->get('app.interactive.purchasing_power_processor_handler');
        $purchasingPower = $handler->start($session);
        $transition = $handler->getCurrentTransition($purchasingPower);

        $form = $this->createForm(PurchasingPowerType::class, $purchasingPower, ['transition' => $transition]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($purchasingPowerLog = $this->get('app.interactive.purchasing_power_processor_handler')->process($session, $purchasingPower)) {
                return $this->redirectToRoute('app_purchasing_power_mail_sent', [
                    'uuid' => $purchasingPowerLog->getUuid()->toString(),
                ]);
            }

            return $this->redirectToRoute('app_purchasing_power');
        }

        return $this->render('interactive/purchasing_power.html.twig', [
            'interactive' => $purchasingPower,
            'interactive_form' => $form->createView(),
            'transition' => $transition,
        ]);
    }

    /**
     * @Route("/ton-pouvoir-achat/recommencer", name="app_purchasing_power_restart")
     * @Method("GET")
     */
    public function restartPurchasingPowerAction(Request $request): Response
    {
        $this->get('app.interactive.purchasing_power_processor_handler')->terminate($request->getSession());

        return $this->redirectToRoute('app_purchasing_power');
    }

    /**
     * @Route("/ton-pouvoir-achat/{uuid}/merci", name="app_purchasing_power_mail_sent")
     * @Method("GET")
     */
    public function mailSentAction(PurchasingPowerInvitation $purchasingPower): Response
    {
        return $this->render('interactive/mail_sent.html.twig', [
            'interactive' => $purchasingPower,
        ]);
    }
}
