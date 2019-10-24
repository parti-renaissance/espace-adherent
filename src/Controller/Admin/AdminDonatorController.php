<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Donation\DonatorMergeCommand;
use AppBundle\Donation\DonatorMergeCommandHandler;
use AppBundle\Form\Admin\DonatorMergeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/donator")
 */
class AdminDonatorController extends Controller
{
    /**
     * @Route("/merge", name="app_admin_donator_merge", methods={"GET", "POST"})
     *
     * @Security("has_role('ROLE_ADMIN_DONATORS')")
     */
    public function mergeAction(Request $request): Response
    {
        $donatorMergeCommand = new DonatorMergeCommand();

        $form = $this
            ->createForm(DonatorMergeType::class, $donatorMergeCommand)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $donatorMergeHandler = $this->get(DonatorMergeCommandHandler::class);

            if ($form->get('confirm')->isClicked()) {
                $donatorMergeHandler->handle($donatorMergeCommand);

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
}
