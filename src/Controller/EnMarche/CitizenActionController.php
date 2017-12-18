<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\EntityControllerTrait;
use AppBundle\Entity\CitizenAction;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @Route("/action-citoyenne")
 * @Entity("action", expr="repository.findOnePublishedBySlug(slug)")
 */
class CitizenActionController extends Controller
{
    use EntityControllerTrait;

    /**
     * @Route("/{slug}", name="app_citizen_action_show")
     * @Method("GET")
     */
    public function showAction(CitizenAction $action): Response
    {
        return $this->render('citizen_action/show.html.twig', [
            'citizen_action' => $action,
        ]);
    }

    /**
     * @Route("/{slug}/ical", name="app_citizen_action_export_ical")
     * @Method("GET")
     */
    public function exportIcalAction(CitizenAction $citizenAction): Response
    {
        $disposition = sprintf('%s; filename=%s.ics', ResponseHeaderBag::DISPOSITION_ATTACHMENT, $citizenAction->getSlug());

        $response = new Response($this->get('jms_serializer')->serialize($citizenAction, 'ical'), Response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/calendar');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
