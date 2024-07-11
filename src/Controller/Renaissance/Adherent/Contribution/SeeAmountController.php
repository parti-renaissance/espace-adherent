<?php

namespace App\Controller\Renaissance\Adherent\Contribution;

use App\Adherent\AdherentRoleEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted(AdherentRoleEnum::ONGOING_ELECTED_REPRESENTATIVE)]
#[Route(path: '/espace-elus/cotisation/montant', name: 'app_renaissance_contribution_see_amount', methods: ['GET'])]
class SeeAmountController extends AbstractContributionController
{
    public function __invoke(Request $request): Response
    {
        $command = $this->getCommand();

        if (!$this->processor->canSeeContributionAmount($command)) {
            return $this->redirectToRoute('app_renaissance_contribution_fill_revenue');
        }

        $this->processor->doSeeContributionAmount($command);

        return $this->render('renaissance/adherent/contribution/see_amount.html.twig', [
            'command' => $command,
        ]);
    }
}
