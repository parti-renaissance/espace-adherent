<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ParrainageReport;
use AppBundle\Form\ParrainageReportType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ParrainageController extends Controller
{
    /**
     * @Route("/parrainage", name="app_parrainage")
     * @Method("GET|POST")
     */
    public function indexAction(Request $request): Response
    {
        $parrainageReport = ParrainageReport::createWithCaptcha((string) $request->request->get('g-recaptcha-response'));

        $form = $this->createForm(ParrainageReportType::class, $parrainageReport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($parrainageReport);
            $em->flush();

            return $this->redirectToRoute('app_parrainage_thanks');
        }

        return $this->render('parrainage/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/parrainage/merci", name="app_parrainage_thanks")
     * @Method("GET")
     */
    public function thanksAction(): Response
    {
        return $this->render('parrainage/thanks.html.twig');
    }

}
