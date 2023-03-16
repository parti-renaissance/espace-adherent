<?php

namespace App\Controller\Renaissance\ElectedRepresentative\Contribution;

use App\Form\Renaissance\ElectedRepresentative\Contribution\RevenueType;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/espace-elus/cotisation', name: 'app_renaissance_elected_representative_contribution_fill_revenue', methods: ['GET|POST'])]
class FillRevenueController extends AbstractContributionController
{
    public function __invoke(
        Request $request,
        ElectedRepresentativeRepository $electedRepresentativeRepository
    ): Response {
        $command = $this->getCommand($request);

        if (!$this->processor->canFillRevenue($command)) {
            return $this->redirectToRoute('app_renaissance_homepage');
        }

        $this->processor->doFillRevenue($command);

        $electedRepresentative = $electedRepresentativeRepository->findOneBy(['adherent' => $this->getUser()]);

        if (!$electedRepresentative) {
            throw $this->createAccessDeniedException(sprintf('No elected representative found for adherent UUID: %s', $this->getUser()->getUuidAsString()));
        }

        if ($electedRepresentative->getLastContributionDate()) {
            $this->processor->doContributionAlreadyDone($command);

            return $this->render('renaissance/elected_representative/contribution/contribution_already_done.html.twig');
        }

        $form = $this
            ->createForm(RevenueType::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$command->needContribution()) {
                $this->processor->doNoContributionNeeded($command);

                return $this->render('renaissance/elected_representative/contribution/no_contribution_needed.html.twig');
            }

            return $this->redirectToRoute('app_renaissance_elected_representative_contribution_see_amount');
        }

        return $this->render('renaissance/elected_representative/contribution/fill_revenue.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
