<?php

namespace App\Controller\EnMarche;

use App\Controller\CanaryControllerTrait;
use App\Entity\MyEuropeInvitation;
use App\Form\MyEuropeType;
use App\Interactive\MyEuropeProcessorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InteractiveController extends AbstractController
{
    use CanaryControllerTrait;

    public const MESSAGE_SUBJECT = 'Pour une Renaissance européenne';

    #[Route(path: '/mon-europe', name: 'app_my_europe', methods: ['GET', 'POST'])]
    public function myEuropeAction(Request $request, MyEuropeProcessorHandler $handler): Response
    {
        $this->disableInProduction();

        $session = $request->getSession();
        $myEurope = $handler->start($session, (string) $request->request->get('g-recaptcha-response'));
        $transition = $handler->getCurrentTransition($myEurope);
        $myEurope->messageSubject = self::MESSAGE_SUBJECT;

        $form = $this
            ->createForm(MyEuropeType::class, $myEurope, ['transition' => $transition])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($myEuropeLog = $handler->process($session, $myEurope)) {
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

    #[Route(path: '/mon-europe/recommencer', name: 'app_my_europe_restart', methods: ['GET'])]
    public function restartMyEuropeAction(Request $request, MyEuropeProcessorHandler $handler): Response
    {
        $this->disableInProduction();

        $handler->terminate($request->getSession());

        return $this->redirectToRoute('app_my_europe');
    }

    #[Route(path: '/mon-europe/{uuid}/merci', name: 'app_my_europe_mail_sent', methods: ['GET'])]
    public function mailSentAction(MyEuropeInvitation $myEurope): Response
    {
        $this->disableInProduction();

        return $this->render('interactive/mail_sent.html.twig', [
            'interactive' => $myEurope,
        ]);
    }
}
