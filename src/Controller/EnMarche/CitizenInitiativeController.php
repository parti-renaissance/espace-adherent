<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\CitizenInitiative\CitizenInitiativeCommand;
use AppBundle\Controller\EntityControllerTrait;
use AppBundle\Entity\Skill;
use AppBundle\Form\CitizenInitiativeType;
use AppBundle\Repository\SkillRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/initiative_citoyenne")
 */
class CitizenInitiativeController extends Controller
{
    use EntityControllerTrait;

    /**
     * @Route("/creer", name="app_create_citizen_initiative")
     * @Method("GET|POST")
     * @Security("is_granted('CREATE_CITIZEN_INITIATIVE')")
     */
    public function createEventAction(Request $request, ?CitizenInitiativeCommand $command): Response
    {
        $command = new CitizenInitiativeCommand($this->getUser());
        $form = $this->createForm(CitizenInitiativeType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.citizen_initiative.handler')->handle($command);
            $this->addFlash('info', $this->get('translator')->trans('citizen_initiative.creation.success'));

            return $this->redirectToRoute('app_search_events');
        }

        return $this->render('citizen_initiative/add_citizen_initiative.html.twig', [
            'initiative' => $command,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/competences/autocompletion",
     *     name="app_citizen_initiative_skills_autocomplete",
     *     condition="request.isXmlHttpRequest()"
     * )
     * @Method("GET")
     * @Security("is_granted('CREATE_CITIZEN_INITIATIVE')")
     */
    public function skillsAutocompleteAction(Request $request)
    {
        $skills = $this->getDoctrine()->getRepository(Skill::class)->findAvailableSkillsFor(
            $this->get('sonata.core.slugify.cocur')->slugify($request->query->get('term')), $this->getUser(), SkillRepository::FIND_FOR_CITIZEN_INITIATIVE);

        return new JsonResponse($skills);
    }
}
