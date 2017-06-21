<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Exception\CommitteeException;
use AppBundle\Exception\CommitteeMembershipException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Route("/committee")
 */
class AdminCommitteeController extends Controller
{
    /**
     * Approves the committee.
     *
     * @Route("/{id}/approve", name="app_admin_committee_approve")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN_COMMITTEES')")
     */
    public function approveAction(Committee $committee): Response
    {
        try {
            $this->get('app.committee.authority')->approve($committee);
            $this->addFlash('sonata_flash_success', sprintf('Le comité « %s » a été approuvé avec succès.', $committee->getName()));
        } catch (CommitteeException $exception) {
            throw $this->createNotFoundException(sprintf('Committee %u must be pending in order to be approved.', $committee->getId()), $exception);
        }

        return $this->redirectToRoute('admin_app_committee_list');
    }

    /**
     * Refuses the committee.
     *
     * @Route("/{id}/refuse", name="app_admin_committee_refuse")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN_COMMITTEES')")
     */
    public function refuseAction(Committee $committee): Response
    {
        try {
            $this->get('app.committee.authority')->refuse($committee);
            $this->addFlash('sonata_flash_success', sprintf('Le comité « %s » a été refusé avec succès.', $committee->getName()));
        } catch (CommitteeException $exception) {
            throw $this->createNotFoundException(sprintf('Committee %u must be pending in order to be refused.', $committee->getId()), $exception);
        }

        return $this->redirectToRoute('admin_app_committee_list');
    }

    /**
     * @Route("/{id}/members", name="app_admin_committee_members")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN_COMMITTEES')")
     */
    public function membersAction(Committee $committee): Response
    {
        $manager = $this->get('app.committee.manager');

        return $this->render('admin/committee/members.html.twig', [
            'committee' => $committee,
            'memberships' => $memberships = $manager->getCommitteeMemberships($committee),
            'supervisors_count' => $memberships->countCommitteeSupervisorMemberships(),
        ]);
    }

    /**
     * @Route("/{committee}/members/{adherent}/set-privilege/{privilege}", name="app_admin_committee_change_privilege")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN_COMMITTEES')")
     */
    public function changePrivilegeAction(Request $request, Committee $committee, Adherent $adherent, string $privilege): Response
    {
        if (!$this->isCsrfTokenValid(sprintf('committee.change_privilege.%s', $adherent->getId()), $request->query->get('token'))) {
            throw new BadRequestHttpException('Invalid Csrf token provided.');
        }

        try {
            $this->get('app.committee.manager')->changePrivilege($adherent, $committee, $privilege);
        } catch (CommitteeMembershipException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_committee_members', [
            'id' => $committee->getId(),
        ]);
    }
}
