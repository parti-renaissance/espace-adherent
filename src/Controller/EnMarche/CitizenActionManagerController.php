<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\CitizenAction\CitizenActionCommand;
use AppBundle\CitizenAction\CitizenActionCommandHandler;
use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\Entity\CitizenProject;
use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Form\CitizenActionCommandType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/projets-citoyens/{project_slug}/actions")
 * @Entity("project", expr="repository.findOneApprovedBySlug(project_slug)")
 * @Entity("action", expr="repository.findOneBySlug(slug)")
 */
class CitizenActionManagerController extends Controller
{
    /**
     * @Route("/creer", name="app_citizen_action_manager_create")
     * @Method("GET|POST")
     * @Security("is_granted('CREATE_CITIZEN_ACTION', project)")
     */
    public function createAction(Request $request, CitizenProject $project, CitizenProjectManager $citizenProjectManager): Response
    {
        $command = new CitizenActionCommand($this->getUser(), $project);
        $form = $this->createForm(CitizenActionCommandType::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $action = $this->get(CitizenActionCommandHandler::class)->handle($command);

            $this->get('app.event.registration_handler')->handle(new EventRegistrationCommand($action, $this->getUser()));
            $this->addFlash('info', $this->get('translator')->trans('citizen_action.creation.success'));

            return $this->redirectToRoute('app_event_show', [
                'slug' => $action->getSlug(),
            ]);
        }

        return $this->render('citizen_action_manager/create.html.twig', [
            'citizen_project' => $project,
            'project_hosts' => $citizenProjectManager->getCitizenProjectAdministrators($project),
            'citizen_action_form' => $form->createView(),
        ]);
    }
}
