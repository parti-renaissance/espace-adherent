<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Exception\BaseGroupException;
use AppBundle\Exception\CitizenProjectMembershipException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Route("/projets-citoyens")
 */
class AdminCitizenProjectController extends Controller
{
    /**
     * Approves the citizen project.
     *
     * @Route("/{id}/approve", name="app_admin_citizenproject_approve")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN_CITIZEN_PROJECTS')")
     */
    public function approveAction(CitizenProject $citizenProject): Response
    {
        try {
            $this->get('app.citizen_project.authority')->approve($citizenProject);
            $this->addFlash('sonata_flash_success', sprintf('Le projet citoyen « %s » a été approuvé avec succès.', $citizenProject->getName()));
        } catch (BaseGroupException $exception) {
            throw $this->createNotFoundException(sprintf('CitizenProject %u must be pending in order to be approved.', $citizenProject->getId()), $exception);
        }

        return $this->redirectToRoute('admin_app_citizenproject_list');
    }

    /**
     * Refuses the citizen project.
     *
     * @Route("/{id}/refuse", name="app_admin_citizenproject_refuse")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN_CITIZEN_PROJECTS')")
     */
    public function refuseAction(CitizenProject $citizenProject): Response
    {
        try {
            $this->get('app.citizen_project.authority')->refuse($citizenProject);
            $this->addFlash('sonata_flash_success', sprintf('Le projet citoyen « %s » a été refusé avec succès.', $citizenProject->getName()));
        } catch (BaseGroupException $exception) {
            throw $this->createNotFoundException(sprintf('CitizenProject %u must be pending in order to be refused.', $citizenProject->getId()), $exception);
        }

        return $this->redirectToRoute('admin_app_citizenproject_list');
    }

    /**
     * @Route("/{id}/members", name="app_admin_citizenproject_members")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN_CITIZEN_PROJECTS')")
     */
    public function membersAction(CitizenProject $citizenProject): Response
    {
        $manager = $this->get('app.citizen_project.manager');

        return $this->render('admin/citizen_project/members.html.twig', [
            'citizenProject' => $citizenProject,
            'memberships' => $memberships = $manager->getCitizenProjectMemberships($citizenProject),
            'administratorsCount' => $memberships->countCitizenProjectAdministratorMemberships(),
        ]);
    }

    /**
     * @Route("/{citizenProject}/members/{adherent}/set-privilege/{privilege}", name="app_admin_citizenproject_change_privilege")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN_CITIZEN_PROJECTS')")
     */
    public function changePrivilegeAction(Request $request, CitizenProject $citizenProject, Adherent $adherent, string $privilege): Response
    {
        if (!$this->isCsrfTokenValid(sprintf('citizen_project.change_privilege.%s', $adherent->getId()), $request->query->get('token'))) {
            throw new BadRequestHttpException('Invalid Csrf token provided.');
        }

        try {
            $this->get('app.citizen_project.manager')->changePrivilege($adherent, $citizenProject, $privilege);
        } catch (CitizenProjectMembershipException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_citizenproject_members', [
            'id' => $citizenProject->getId(),
        ]);
    }
}
