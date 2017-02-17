<?php

namespace AppBundle\Controller;

use AppBundle\Entity\JeMarcheReport;
use AppBundle\Form\JeMarcheReportType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JeMarcheController extends Controller
{
    /**
     * @Route("/je-marche", name="app_je_marche")
     * @Method("GET|POST")
     */
    public function indexAction(Request $request): Response
    {
        $jeMarcheReport = JeMarcheReport::createWithCaptcha((string) $request->request->get('g-recaptcha-response'));

        $form = $this->createForm(JeMarcheReportType::class, $jeMarcheReport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($jeMarcheReport);
            $em->flush();

            return $this->redirectToRoute('app_je_marche_thanks');
        }

        return $this->render('jemarche/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/je-marche/merci", name="app_je_marche_thanks")
     * @Method("GET")
     */
    public function thanksAction(): Response
    {
        return $this->render('jemarche/thanks.html.twig');
    }
}
