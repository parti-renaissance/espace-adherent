<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/procuration")
 */
class AdminProcurationController extends Controller
{
    /**
     * List the procuration referents invitations URLs.
     *
     * @Route("/referents-invitation-urls", name="app_admin_procuration_referents_invitations_urls")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN_PROCURATIONS')")
     */
    public function referentsInvitationUrlsAction(): Response
    {
        $referents = $this->getDoctrine()->getRepository(Adherent::class)->findReferents();

        return $this->render('admin/procuration/referents_invitation_urls.html.twig', [
            'referents' => $referents,
        ]);
    }

    /**
     * @Route("/export")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN_PROCURATIONS')")
     */
    public function exportMailsAction(): Response
    {
        $requests = $this->getDoctrine()->getRepository(ProcurationRequest::class)->findAllForExport();
        $exported = $this->get('app.procuration.request_serializer')->serialize($requests);

        return new Response($exported, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="procurations-matched.csv"',
        ]);
    }

    /**
     * List the procuration referents invitations URLs.
     *
     * @Route("/request/{id}/deassociate", name="app_admin_procuration_request_deassociate")
     * @Method("GET|POST")
     * @Security("has_role('ROLE_ADMIN_PROCURATIONS')")
     */
    public function deassociateAction(Request $sfRequest, ProcurationRequest $request): Response
    {
        if (!$request->getFoundProxy()) {
            return $this->redirectAfterDeassociation($sfRequest);
        }

        $form = $this->createForm(FormType::class);
        $form->add('submit', SubmitType::class);
        $form->handleRequest($sfRequest);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.procuration.process_handler')->unprocess(null, $request);

            return $this->redirectAfterDeassociation($sfRequest);
        }

        return $this->render('admin/procuration/request_deassociate.html.twig', [
            'form' => $form->createView(),
            'request' => $request,
        ]);
    }

    private function redirectAfterDeassociation(Request $request)
    {
        if ('proxies' === $request->query->get('from')) {
            return $this->redirectToRoute('admin_app_procurationproxy_list');
        }

        return $this->redirectToRoute('admin_app_procurationrequest_list');
    }
}
