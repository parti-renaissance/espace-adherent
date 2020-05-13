<?php

namespace App\Controller\EnMarche;

use App\CitizenProject\CitizenProjectContactActorsCommand;
use App\CitizenProject\CitizenProjectContactActorsCommandHandler;
use App\CitizenProject\CitizenProjectManager;
use App\CitizenProject\CitizenProjectUpdateCommand;
use App\Entity\CitizenProject;
use App\Form\CitizenProjectCommandType;
use App\Form\CitizenProjectContactActorsType;
use App\Utils\GroupUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/projets-citoyens/{slug}")
 * @Security("is_granted('ADMINISTRATE_CITIZEN_PROJECT', citizenProject)")
 */
class CitizenProjectManagerController extends Controller
{
    /**
     * @Route("/editer", name="app_citizen_project_manager_edit", methods={"GET", "POST"})
     */
    public function editAction(
        Request $request,
        CitizenProject $citizenProject,
        CitizenProjectManager $manager
    ): Response {
        $command = CitizenProjectUpdateCommand::createFromCitizenProject($citizenProject);
        $form = $this->createForm(CitizenProjectCommandType::class, $command, [
            'from_turnkey_project' => $citizenProject->isFromTurnkeyProject(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.citizen_project.update_handler')->handle($command);
            $this->addFlash('info', 'citizen_project.update.success');

            return $this->redirectToRoute('app_citizen_project_manager_edit', [
                'slug' => $citizenProject->getSlug(),
            ]);
        }

        return $this->render('citizen_project/edit.html.twig', [
            'form' => $form->createView(),
            'citizen_project' => $citizenProject,
            'administrators' => $manager->getCitizenProjectAdministrators($citizenProject),
            'form_committee_support' => $this->createForm(FormType::class)->createView(),
        ]);
    }

    /**
     * @Route("/acteurs", name="app_citizen_project_list_actors", methods={"GET"})
     *
     * @Security("citizenProject.isApproved()")
     */
    public function listActorsAction(
        CitizenProject $citizenProject,
        CitizenProjectManager $citizenProjectManager
    ): Response {
        return $this->render('citizen_project/list_actors.html.twig', [
            'citizen_project' => $citizenProject,
            'administrators' => $citizenProjectManager->getCitizenProjectAdministrators($citizenProject),
            'form_committee_support' => $this->createForm(FormType::class)->createView(),
            'actors' => $citizenProjectManager->getCitizenProjectMemberships($citizenProject),
        ]);
    }

    /**
     * @Route("/acteurs/contact", name="app_citizen_project_contact_actors", methods={"POST"})
     */
    public function contactActorsAction(
        Request $request,
        CitizenProject $citizenProject,
        CitizenProjectManager $citizenProjectManager
    ): Response {
        if (!$this->isCsrfTokenValid('citizen_project.contact_actors', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to contact actors.');
        }

        $uuids = GroupUtils::getUuidsFromJson($request->request->get('contacts', ''));
        $adherents = GroupUtils::removeUnknownAdherents($uuids, $citizenProjectManager->getCitizenProjectMembers($citizenProject));
        $command = new CitizenProjectContactActorsCommand($adherents, $this->getUser());
        $contacts = GroupUtils::getUuidsFromAdherents($adherents);

        if (empty($contacts)) {
            $this->addFlash('info', 'citizen_project.contact_actors.none');

            return $this->redirectToRoute('app_citizen_project_list_actors', [
                'slug' => $citizenProject->getSlug(),
            ]);
        }

        $form = $this->createForm(CitizenProjectContactActorsType::class, $command)
            ->add('submit', SubmitType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get(CitizenProjectContactActorsCommandHandler::class)->handle($command);
            $this->addFlash('info', 'citizen_project.contact_actors.success');

            return $this->redirectToRoute('app_citizen_project_list_actors', [
                'slug' => $citizenProject->getSlug(),
            ]);
        }

        return $this->render('citizen_project/contact.html.twig', [
            'citizen_project' => $citizenProject,
            'administrators' => $citizenProjectManager->getCitizenProjectAdministrators($citizenProject),
            'contacts' => GroupUtils::getUuidsFromAdherents($adherents),
            'form_committee_support' => $this->createForm(FormType::class)->createView(),
            'form' => $form->createView(),
        ]);
    }
}
