<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\CitizenProject\CitizenProjectCommentCommand;
use AppBundle\CitizenProject\CitizenProjectCommentCreationCommandHandler;
use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\CitizenProject\CitizenProjectPermissions;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectCategory;
use AppBundle\Entity\CitizenProjectCategorySkill;
use AppBundle\Entity\Committee;
use AppBundle\Exception\CitizenProjectCommitteeSupportAlreadySupportException;
use AppBundle\Exception\CitizenProjectNotApprovedException;
use AppBundle\Form\CitizenProjectCommentCommandType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/projets-citoyens")
 */
class CitizenProjectController extends Controller
{
    /**
     * @Route("/{slug}", name="app_citizen_project_show")
     * @Method("GET|POST")
     * @Security("is_granted('SHOW_CITIZEN_PROJECT', citizenProject)")
     */
    public function showAction(Request $request, CitizenProject $citizenProject, CitizenProjectManager $citizenProjectManager): Response
    {
        $form = null;

        if ($this->isGranted(CitizenProjectPermissions::COMMENT, $citizenProject)) {
            $commentCommand = new CitizenProjectCommentCommand($citizenProject, $this->getUser());
            $form = $this->createForm(CitizenProjectCommentCommandType::class, $commentCommand)
                ->handleRequest($request)
            ;

            if ($form->isSubmitted() && $form->isValid()) {
                $this->get(CitizenProjectCommentCreationCommandHandler::class)->handle($commentCommand);
                $this->addFlash('info', 'Ton commentaire a été ajoutée');

                return $this->redirectToRoute('app_citizen_project_show', ['slug' => $citizenProject->getSlug()]);
            }
        }

        return $this->render('citizen_project/show.html.twig', [
            'citizen_project' => $citizenProject,
            'citizen_actions' => $citizenProjectManager->getCitizenProjectNextActions($citizenProject),
            'form_committee_support' => $this->createForm(FormType::class)->createView(),
            'administrators' => $citizenProjectManager->getCitizenProjectAdministrators($citizenProject),
            'followers' => $citizenProjectManager->getCitizenProjectFollowers($citizenProject),
            'comments' => $citizenProjectManager->getCitizenProjectComments($citizenProject),
            'form' => $form ? $form->createView() : null,
        ]);
    }

    /**
     * @Route("/aide", name="app_citizen_project_help")
     * @Method("GET|POST")
     */
    public function helpAction(): Response
    {
        return new Response();
    }

    /**
     * @Route("/skills/autocompletion",
     *     name="app_citizen_project_skills_autocomplete",
     *     condition="request.isXmlHttpRequest() and request.query.get('category') and request.query.get('term')"
     * )
     * @Method("GET")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function skillsAutocompleteAction(Request $request)
    {
        if (!$category = $this->getDoctrine()->getRepository(CitizenProjectCategory::class)->find($request->query->get('category'))) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }

        $citizenProjectCategorySkills = $this
            ->getDoctrine()
            ->getRepository(CitizenProjectCategorySkill::class)
            ->findByCitizenProjectCategoryAndTerm($category, $request->query->get('term', ''));

        /** @var CitizenProjectCategorySkill[] $citizenProjectCategorySkills */
        foreach ($citizenProjectCategorySkills as $citizenProjectCategorySkill) {
            $result[] = [
                'id' => $citizenProjectCategorySkill->getSkill()->getId(),
                'name' => $citizenProjectCategorySkill->getSkill()->getName(),
            ];
        }

        return new JsonResponse($result ?? []);
    }

    /**
     * @Route("/comite/autocompletion",
     *     name="app_citizen_project_committee_autocomplete",
     *     condition="request.isXmlHttpRequest()"
     * )
     * @Method("GET")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function committeeAutocompleteAction(Request $request)
    {
        if (!$term = $request->query->get('term')) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        $committees = $this
            ->getDoctrine()
            ->getRepository(Committee::class)
            ->findByPartialName($term);

        foreach ($committees as $committee) {
            $result[] = [
                'uuid' => $committee->getUuid()->toString(),
                'name' => $committee->getName(),
            ];
        }

        return new JsonResponse($result ?? []);
    }

    /**
     * @Route("/mon-comite-soutien/{slug}", name="app_citizen_project_committee_support")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @Method("GET|POST")
     */
    public function committeeSupportAction(Request $request, CitizenProject $citizenProject, CitizenProjectManager $citizenProjectManager): Response
    {
        $user = $this->getUser();
        if (!$user->isSupervisor()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $committeeUuid = $user->getMemberships()->getCommitteeSupervisorMemberships()->last()->getCommitteeUuid();

            try {
                $citizenProjectManager->approveCommitteeSupport(
                    $this->getDoctrine()->getRepository(Committee::class)->findOneByUuid($committeeUuid),
                    $citizenProject
                );
                $flashMessage = sprintf('Votre comité soutient maintenant le projet citoyen %s', $citizenProject->getName());
            } catch (CitizenProjectCommitteeSupportAlreadySupportException $committeeSupportAlreadySupportException) {
                $flashMessage = sprintf(
                    'Votre comité %s soutient déjà le projet citoyen %s',
                    $committeeSupportAlreadySupportException->getCommittee()->getName(),
                    $committeeSupportAlreadySupportException->getCitizenProject()->getName()
                );
            } catch (CitizenProjectNotApprovedException $approvedException) {
                throw $this->createAccessDeniedException();
            }

            $this->addFlash('info', $flashMessage);

            return $this->redirectToRoute('app_citizen_project_show', [
                'slug' => $citizenProject->getSlug(),
            ]);
        }

        return $this->render('citizen_project/committee_confirm_support.html.twig', [
            'form' => $form->createView(),
            'citizen_project' => $citizenProject,
        ]);
    }

    /**
     * @Route("/{slug}/acteurs", name="app_citizen_project_list_actors")
     * @Method("GET")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function listActorsAction(CitizenProject $citizenProject, CitizenProjectManager $citizenProjectManager): Response
    {
        return $this->render('citizen_project/list_actors.html.twig', [
            'citizen_project' => $citizenProject,
            'administrators' => $citizenProjectManager->getCitizenProjectAdministrators($citizenProject),
            'followers' => $citizenProjectManager->getCitizenProjectFollowers($citizenProject),
            'form_committee_support' => $this->createForm(FormType::class)->createView(),
            'actors' => $citizenProjectManager->getCitizenProjectMemberships($citizenProject),
        ]);
    }

    /**
     * @Route("/{slug}/rejoindre", name="app_citizen_project_follow", condition="request.request.has('token')")
     * @Method("POST")
     * @Security("is_granted('FOLLOW_CITIZEN_PROJECT', citizenProject)")
     */
    public function followAction(Request $request, CitizenProject $citizenProject): Response
    {
        if (!$this->isCsrfTokenValid('citizen_project.follow', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to follow citizen project.');
        }

        $this->get('app.citizen_project.authority')->followCitizenProject($this->getUser(), $citizenProject);

        return new JsonResponse([
            'button' => [
                'label' => 'Quitter ce projet citoyen',
                'action' => 'quitter',
                'csrf_token' => (string) $this->get('security.csrf.token_manager')->getToken('citizen_project.unfollow'),
            ],
        ]);
    }

    /**
     * @Route("/{slug}/quitter", name="app_citizen_project_unfollow", condition="request.request.has('token')")
     * @Method("POST")
     * @Security("is_granted('UNFOLLOW_CITIZEN_PROJECT', citizenProject)")
     */
    public function unfollowAction(Request $request, CitizenProject $citizenProject): Response
    {
        if (!$this->isCsrfTokenValid('citizen_project.unfollow', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to unfollow citizen project.');
        }

        $this->get(CitizenProjectManager::class)->unfollowCitizenProject($this->getUser(), $citizenProject);

        return new JsonResponse([
            'button' => [
                'label' => 'Suivre ce projet citoyen',
                'action' => 'rejoindre',
                'csrf_token' => (string) $this->get('security.csrf.token_manager')->getToken('citizen_project.follow'),
            ],
        ]);
    }
}
