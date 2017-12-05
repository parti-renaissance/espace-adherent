<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectCategory;
use AppBundle\Entity\CitizenProjectCategorySkill;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
}
