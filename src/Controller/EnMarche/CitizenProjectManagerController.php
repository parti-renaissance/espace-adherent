<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\CitizenProject\CitizenProjectCommand;
use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\CitizenProject;
use AppBundle\Form\CitizenProjectCommandType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/projets-citoyens/{slug}")
 * @Security("is_granted('ADMINISTRATE_CITIZEN_PROJECT', citizenProject)")
 */
class CitizenProjectManagerController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route("/editer", name="app_citizen_project_manager_edit")
     * @Method("GET|POST")
     */
    public function editAction(Request $request, CitizenProject $citizenProject, CitizenProjectManager $manager): Response
    {
        $this->disableInProduction();

        $command = CitizenProjectCommand::createFromCitizenProject($citizenProject);
        $form = $this->createForm(CitizenProjectCommandType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.citizen_project.update_handler')->handle($command);
            $this->addFlash('info', $this->get('translator')->trans('citizen_project.update.success'));

            return $this->redirectToRoute('app_citizen_project_manager_edit', [
                'slug' => $citizenProject->getSlug(),
            ]);
        }

        return $this->render('citizen_project/edit.html.twig', [
            'form' => $form->createView(),
            'citizen_project' => $citizenProject,
            'administrators' => $manager->getCitizenProjectAdministrators($citizenProject),
            'followers' => $manager->getCitizenProjectFollowers($citizenProject),
            'form_committee_support' => $this->createForm(FormType::class)->createView(),
        ]);
    }
}
