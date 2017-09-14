<?php

namespace AppBundle\Controller\Admin;

use AppBundle\CitizenInitiative\CitizenInitiativeManager;
use AppBundle\Entity\CitizenInitiative;
use AppBundle\Entity\Skill;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/initiative-citoyenne")
 * @Security("has_role('ROLE_ADMIN_CITIZEN_INITIATIVES')")
 */
class AdminCitizenInitiativeController extends Controller
{
    /**
     * @Route("/{uuid}/{slug}/changer_besoin_d_un_expert", name="app_admin_citizen_initiative_change_expert_assistance", condition="request.isXmlHttpRequest()")
     * @Method("GET")
     */
    public function changeExpertAssistanceAction(CitizenInitiative $initiative)
    {
        $this->get(CitizenInitiativeManager::class)->changeExpertStatusCitizenInitiative($initiative);

        return $this->redirectToRoute('admin_app_asking_help_citizeninitiative_list');
    }

    /**
     * @Route("/competences/autocompletion/admin",
     *     name="app_admin_citizen_initiative_skills_autocomplete",
     *     condition="request.isXmlHttpRequest()"
     * )
     * @Method("GET")
     */
    public function skillsAutocompleteAction(Request $request)
    {
        $skills = $this->getDoctrine()->getRepository(Skill::class)->findAvailableSkillsForAdmin(
            $this->get('sonata.core.slugify.cocur')->slugify($request->query->get('term')));

        return new JsonResponse($skills);
    }

    /**
     * @Route("/{uuid}/publish", name="app_admin_citizen_initiative_publish")
     * @Method("GET")
     */
    public function publishAction(Request $request, CitizenInitiative $initiative): Response
    {
        $this->get(CitizenInitiativeManager::class)->publishCitizenInitiative($initiative);
        $this->get('sonata.core.flashmessage.manager')->getSession()->getFlashBag()->set('success', sprintf('L\'élément "%s" a été mis à jour avec succès.', $initiative->getName()));

        return $this->redirect($request->headers->get('referer'));
    }
}
