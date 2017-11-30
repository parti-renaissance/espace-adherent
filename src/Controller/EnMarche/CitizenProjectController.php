<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\CitizenProject;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/projets-citoyens")
 */
class CitizenProjectController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route("/{slug}", name="app_citizen_project_show")
     * @Method("GET|POST")
     * @Security("is_granted('SHOW_CITIZEN_PROJECT', citizenProject)")
     */
    public function showAction(CitizenProject $citizenProject): Response
    {
        $this->disableInProduction();

        $citizenProjectManager = $this->get('app.citizen_project.manager');

        return $this->render('citizen_project/show.html.twig', [
            'citizen_project' => $citizenProject,
            'citizen_project_administrators' => $citizenProjectManager->getCitizenProjectAdministrators($citizenProject),
            'citizen_project_followers' => $citizenProjectManager->getCitizenProjectFollowers($citizenProject),
        ]);
    }

    /**
     * @Route("/aide", name="app_citizen_project_help")
     * @Method("GET|POST")
     */
    public function helpAction(): Response
    {
        $this->disableInProduction();

        return new Response();
    }
}
