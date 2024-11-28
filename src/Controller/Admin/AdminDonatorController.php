<?php

namespace App\Controller\Admin;

use App\Donation\Command\DonatorExtractCommand;
use App\Donation\Command\DonatorMergeCommand;
use App\Donation\Handler\DonatorExtractCommandHandler;
use App\Donation\Handler\DonatorMergeCommandHandler;
use App\Form\Admin\DonatorMergeType;
use App\Form\Admin\Extract\DonatorExtractType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN_FINANCES_DONATIONS')]
#[Route(path: '/donator')]
class AdminDonatorController extends AbstractController
{
    #[Route(path: '/merge', name: 'app_admin_donator_merge', methods: ['GET', 'POST'])]
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

    #[Route(path: '/extract', name: 'app_admin_donator_extract', methods: ['GET', 'POST'])]
    public function extractAction(
        Request $request,
        DonatorExtractCommandHandler $donatorExtractCommandHandler,
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
