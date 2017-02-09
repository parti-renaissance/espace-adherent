<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Committee;
use AppBundle\Exception\CommitteeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
     */
    public function approveAction(Committee $committee): Response
    {
        try {
            $this->get('app.committee_authority')->approve($committee);
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
     */
    public function refuseAction(Committee $committee): Response
    {
        try {
            $this->get('app.committee_authority')->refuse($committee);
            $this->addFlash('sonata_flash_success', sprintf('Le comité « %s » a été refusé avec succès.', $committee->getName()));
        } catch (CommitteeException $exception) {
            throw $this->createNotFoundException(sprintf('Committee %u must be pending in order to be refused.', $committee->getId()), $exception);
        }

        return $this->redirectToRoute('admin_app_committee_list');
    }
}
