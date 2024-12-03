<?php

namespace App\Controller\EnMarche;

use App\Entity\JeMarcheReport;
use App\Form\JeMarcheReportType;
use App\JeMarche\JeMarcheReportHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class JeMarcheController extends AbstractController
{
    #[Route(path: '/jagis', name: 'app_je_marche', methods: ['GET'])]
    public function indexAction(): Response
    {
        return $this->render('jemarche/index.html.twig');
    }

    #[Route(path: '/jemarche', name: 'app_je_marche_action', methods: ['GET', 'POST'])]
    public function actionAction(Request $request, JeMarcheReportHandler $handler): Response
    {
        $jeMarcheReport = JeMarcheReport::createWithCaptcha((string) $request->request->get('g-recaptcha-response'));

        $form = $this->createForm(JeMarcheReportType::class, $jeMarcheReport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $handler->handle($jeMarcheReport);

            return $this->redirectToRoute('app_je_marche_thanks');
        }

        return $this->render('jemarche/action.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/jemarche/merci', name: 'app_je_marche_thanks', methods: ['GET'])]
    public function thanksAction(): Response
    {
        return $this->render('jemarche/thanks.html.twig');
    }

    #[Route(path: '/je-marche', name: 'app_je_marche_redirect', methods: ['GET'])]
    public function redirectAction(): Response
    {
        return $this->redirectToRoute('app_je_marche_action', [], Response::HTTP_MOVED_PERMANENTLY);
    }
}
