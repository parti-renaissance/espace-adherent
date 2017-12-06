<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectCommitteeSupport;
use AppBundle\Entity\Committee;
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
     * @Route("/comite/autocompletion",
     *     name="app_citizen_project_committee_autocomplete",
     *     condition="request.isXmlHttpRequest()"
     * )
     * @Method("GET")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function committeeAutocompleteAction(Request $request)
    {
        if (!$request->query->get('term', false)) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        $committees = $this
            ->getDoctrine()
            ->getRepository(Committee::class)
            ->findByPartialName($request->query->get('term'));

        $result = [];

        foreach ($committees as $committee) {
            $result[] = [
                'uuid' => $committee->getUuid()->toString(),
                'name' => $committee->getName(),
            ];
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/mon-comite-soutien/{slug}", name="app_citizen_project_committee_support")
     * @Method("GET")
     */
    public function committeeSupportAction(CitizenProject $citizenProject): Response
    {
        if (!$this->getUser()->isSupervisor()) {
            throw $this->createAccessDeniedException();
        }

        $committeeManager = $this->get('app.committee.manager');
        $flashMessage = sprintf('Le projet citoyen %s n\'a pas demandé votre soutient', $citizenProject->getName());

        /** @var CitizenProjectCommitteeSupport $committeeSupport */
        foreach ($citizenProject->getCommitteeSupports() as $committeeSupport) {
            if ($committeeManager->superviseCommittee($this->getUser(), $committeeSupport->getCommittee())) {
                if ($committeeSupport->isApprove()) {
                    $flashMessage = sprintf('Votre comité %s soutient déjà le projet citoyen %s',
                        $committeeSupport->getCommittee()->getName(),
                        $citizenProject->getName()
                    );

                    break;
                }

                if ($committeeSupport->isPending()) {
                    $committeeSupport->approve();
                    $this->getDoctrine()->getManager()->persist($citizenProject);
                    $this->getDoctrine()->getManager()->flush();
                    $flashMessage = sprintf('Votre comité %s soutient maintenant le projet citoyen %s',
                        $committeeSupport->getCommittee()->getName(),
                        $citizenProject->getName()
                    );

                    break;
                }
            }
        }

        $this->addFlash('info', $flashMessage);

        return $this->redirectToRoute('app_citizen_project_show', [
            'slug' => $citizenProject->getSlug(),
        ]);
    }
}
