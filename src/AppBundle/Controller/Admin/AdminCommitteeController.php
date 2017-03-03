<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Exception\CommitteeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

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
     * @Security("has_role('ROLE_TERRITORY')")
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
     * @Security("has_role('ROLE_TERRITORY')")
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
     * @Security("has_role('ROLE_TERRITORY')")
     */
    public function membersAction(Committee $committee): Response
    {
        $manager = $this->get('app.committee.manager');

        return $this->render('admin/committee_members.html.twig', [
            'committee' => $committee,
            'hosts' => $manager->getCommitteeHosts($committee),
            'members' => $manager->getCommitteeFollowers($committee, CommitteeManager::EXCLUDE_HOSTS),
        ]);
    }

    /**
     * @Route("/{id}/members/{userId}/set-role/{role}", name="app_admin_committee_change_role")
     * @Method("GET")
     * @Security("has_role('ROLE_TERRITORY')")
     */
    public function changeRoleAction(Committee $committee, $userId, $role): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $adherent = $manager->getRepository(Adherent::class)->find($userId);

        if ($adherent) {
            $repository = $manager->getRepository(CommitteeMembership::class);
            $membership = $repository->findMembership($adherent, $committee->getUuid()->toString());

            if ($membership) {
                if ('host' === $role) {
                    $membership->setPrivilege(CommitteeMembership::COMMITTEE_HOST);
                } else {
                    $membership->setPrivilege(CommitteeMembership::COMMITTEE_FOLLOWER);
                }

                $manager->persist($membership);
                $manager->flush();
            }
        }

        return $this->redirectToRoute('app_admin_committee_members', [
            'id' => $committee->getId(),
        ]);
    }
}
