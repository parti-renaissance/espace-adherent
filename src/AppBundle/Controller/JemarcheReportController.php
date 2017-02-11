<?php

namespace AppBundle\Controller;

use AppBundle\Entity\JemarcheReport;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Jemarchereport controller.
 *
 * @Route("je-marche")
 */
class JemarcheReportController extends Controller
{

    /**
     * Creates a new jemarcheReport entity.
     *
     * @Route("/", name="jemarchereport")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $jemarcheReport = new Jemarchereport();
        $form = $this->createForm('AppBundle\Form\JemarcheReportType', $jemarcheReport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($jemarcheReport);
            $em->flush($jemarcheReport);

            return $this->redirectToRoute('jemarchereport_index');
        }

        return $this->render('jemarchereport/new.html.twig', array(
            'jemarcheReport' => $jemarcheReport,
            'form' => $form->createView(),
        ));
    }

    /**
     * Message after new submit
     *
     * @Route("/merci", name="jemarchereport_merci")
     * @Method("GET")
     */
    public function merciAction()
    {
        return $this->render('jemarchereport/merci.html.twig');
    }
}
