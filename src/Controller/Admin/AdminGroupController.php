<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Group;
use AppBundle\Exception\BaseGroupException;
use AppBundle\Exception\GroupMembershipException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Route("/group")
 */
class AdminGroupController extends Controller
{
    /**
     * Approves the group.
     *
     * @Route("/{id}/approve", name="app_admin_group_approve")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN_GROUPS')")
     */
    public function approveAction(Group $group): Response
    {
        try {
            $this->get('app.group.authority')->approve($group);
            $this->addFlash('sonata_flash_success', sprintf('L\'équipe MOOC « %s » a été approuvé avec succès.', $group->getName()));
        } catch (BaseGroupException $exception) {
            throw $this->createNotFoundException(sprintf('Group %u must be pending in order to be approved.', $group->getId()), $exception);
        }

        return $this->redirectToRoute('admin_app_group_list');
    }

    /**
     * Refuses the group.
     *
     * @Route("/{id}/refuse", name="app_admin_group_refuse")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN_GROUPS')")
     */
    public function refuseAction(Group $group): Response
    {
        try {
            $this->get('app.group.authority')->refuse($group);
            $this->addFlash('sonata_flash_success', sprintf('L\'équipe MOOC « %s » a été refusé avec succès.', $group->getName()));
        } catch (BaseGroupException $exception) {
            throw $this->createNotFoundException(sprintf('Group %u must be pending in order to be refused.', $group->getId()), $exception);
        }

        return $this->redirectToRoute('admin_app_group_list');
    }

    /**
     * @Route("/{id}/members", name="app_admin_group_members")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN_GROUPS')")
     */
    public function membersAction(Group $group): Response
    {
        $manager = $this->get('app.group.manager');

        return $this->render('admin/group/members.html.twig', [
            'group' => $group,
            'memberships' => $memberships = $manager->getGroupMemberships($group),
            'administrators_count' => $memberships->countGroupAdministratorMemberships(),
        ]);
    }

    /**
     * @Route("/{group}/members/{adherent}/set-privilege/{privilege}", name="app_admin_group_change_privilege")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN_GROUPS')")
     */
    public function changePrivilegeAction(Request $request, Group $group, Adherent $adherent, string $privilege): Response
    {
        if (!$this->isCsrfTokenValid(sprintf('group.change_privilege.%s', $adherent->getId()), $request->query->get('token'))) {
            throw new BadRequestHttpException('Invalid Csrf token provided.');
        }

        try {
            $this->get('app.group.manager')->changePrivilege($adherent, $group, $privilege);
        } catch (GroupMembershipException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_group_members', [
            'id' => $group->getId(),
        ]);
    }
}
