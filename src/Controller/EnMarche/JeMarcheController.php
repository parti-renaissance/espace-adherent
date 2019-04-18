<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\JeMarcheReport;
use AppBundle\Form\JeMarcheReportType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JeMarcheController extends Controller
{
    /**
     * @Route("/jagis", name="app_je_marche")
     * @Method("GET")
     */
    public function indexAction(): Response
    {
        return $this->render('jemarche/index.html.twig');
    }

    /**
     * @Route("/jemarche", name="app_je_marche_action")
     * @Method("GET|POST")
     */
    public function actionAction(Request $request): Response
    {
        $jeMarcheReport = JeMarcheReport::createWithCaptcha((string) $request->request->get('g-recaptcha-response'));

        $form = $this->createForm(JeMarcheReportType::class, $jeMarcheReport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.je_marche_report_handler')->handle($jeMarcheReport);

            return $this->redirectToRoute('app_je_marche_thanks');
        }

        return $this->render('jemarche/action.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/jemarche/merci", name="app_je_marche_thanks")
     * @Method("GET")
     */
    public function thanksAction(): Response
    {
        return $this->render('jemarche/thanks.html.twig');
    }

    /**
     * @Route("/je-marche", name="app_je_marche_redirect")
     * @Method("GET")
     */
    public function redirectAction(): Response
    {
        return $this->redirectToRoute('app_je_marche_action', [], Response::HTTP_MOVED_PERMANENTLY);
    }
}
