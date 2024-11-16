<?php

namespace App\Controller\Renaissance\Adherent\Contribution;

use App\Adherent\AdherentRoleEnum;
use App\Adherent\Contribution\ContributionRequestHandler;
use App\Form\Renaissance\Adherent\Contribution\InformationsType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(AdherentRoleEnum::ONGOING_ELECTED_REPRESENTATIVE)]
#[Route(path: '/espace-elus/cotisation/informations', name: 'app_renaissance_contribution_fill_informations', methods: ['GET|POST'])]
class FillInformationsController extends AbstractContributionController
{
    public function __invoke(Request $request, ContributionRequestHandler $contributionRequestHandler): Response
    {
        $command = $this->getCommand();

        if (!$this->processor->canFillContributionInformations($command)) {
            return $this->redirectToRoute('app_renaissance_contribution_see_amount');
        }

        $this->processor->doFillContributionInformations($command);

        $form = $this
            ->createForm(InformationsType::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $contributionRequestHandler->handle($command, $this->getUser());

            $this->processor->doCompleteContributionRequest($command);

            return $this->render('renaissance/adherent/contribution/confirmation.html.twig');
        }

        return $this->render('renaissance/adherent/contribution/fill_informations.html.twig', [
            'form' => $form->createView(),
            'command' => $command,
        ]);
    }
}
