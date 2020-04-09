<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Donation\DonatorExtractCommand;
use AppBundle\Donation\DonatorExtractCommandHandler;
use AppBundle\Donation\DonatorMergeCommand;
use AppBundle\Donation\DonatorMergeCommandHandler;
use AppBundle\Form\Admin\DonatorExtractType;
use AppBundle\Form\Admin\DonatorMergeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/donator")
 *
 * @Security("has_role('ROLE_ADMIN_FINANCE')")
 */
class AdminDonatorController extends Controller
{
    /**
     * @Route("/merge", name="app_admin_donator_merge", methods={"GET", "POST"})
     */
    public function mergeAction(Request $request, DonatorMergeCommandHandler $donatorMergeCommandHandler): Response
    {
        $donatorMergeCommand = new DonatorMergeCommand();

        $form = $this
            ->createForm(DonatorMergeType::class, $donatorMergeCommand)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('confirm')->isClicked()) {
                $donatorMergeCommandHandler->handle($donatorMergeCommand);

                $this->addFlash('success', 'Fusion effectuée avec succès!');

                return $this->redirectToRoute('admin_app_donator_edit', ['id' => $form->get('destinationDonator')->getData()->getId()]);
            }

            return $this->render('admin/donator/merge/confirm.html.twig', [
                'form' => $form->createView(),
                'source_donator' => $donatorMergeCommand->getSourceDonator(),
                'destination_donator' => $donatorMergeCommand->getDestinationDonator(),
            ]);
        }

        return $this->render('admin/donator/merge/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/extract", name="app_admin_donator_extract", methods={"GET", "POST"})
     */
    public function extractAction(
        Request $request,
        DonatorExtractCommandHandler $donatorExtractCommandHandler
    ): Response {
        $donatorExtractCommand = new DonatorExtractCommand();

        $form = $this
            ->createForm(DonatorExtractType::class, $donatorExtractCommand)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            return $donatorExtractCommandHandler->createResponse($donatorExtractCommand);
        }

        return $this->render('admin/donator/extract/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
