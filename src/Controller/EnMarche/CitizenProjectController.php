<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectCategory;
use AppBundle\Entity\CitizenProjectCategorySkill;
use AppBundle\Entity\Committee;
use AppBundle\Exception\CitizenProjectCommitteeSupportAlreadySupportException;
use AppBundle\Exception\CitizenProjectNotApprovedException;
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
            'form_committee_support' => $this->createForm(FormType::class)->createView(),
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
    public function committeeSupportAction(Request $request, CitizenProject $citizenProject): Response
    {
        $user = $this->getUser();
        if (!$user->isSupervisor()) {
            throw $this->createAccessDeniedException();
        }

        $citizenProjectManager = $this->get('app.citizen_project.manager');
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
}
