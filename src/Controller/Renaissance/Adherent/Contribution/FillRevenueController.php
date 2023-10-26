<?php

namespace App\Controller\Renaissance\Adherent\Contribution;

use App\Adherent\AdherentRoleEnum;
use App\Adherent\Contribution\ContributionRequestHandler;
use App\Adherent\Contribution\ContributionStatusEnum;
use App\Adherent\Tag\Command\RefreshAdherentTagCommand;
use App\Entity\Adherent;
use App\Form\Renaissance\Adherent\Contribution\RevenueType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/espace-elus/cotisation', name: 'app_renaissance_contribution_fill_revenue', methods: ['GET|POST'])]
#[IsGranted(AdherentRoleEnum::ONGOING_ELECTED_REPRESENTATIVE)]
class FillRevenueController extends AbstractContributionController
{
    public function __invoke(
        Request $request,
        EntityManagerInterface $entityManager,
        ContributionRequestHandler $contributionRequestHandler,
        MessageBusInterface $bus
    ): Response {
        $command = $this->getCommand($request);

        if (!$this->processor->canFillRevenue($command)) {
            return $this->redirectToRoute('app_renaissance_adherent_space');
        }

        $this->processor->doFillRevenue($command);

        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if (!$command->isRedeclare() && $adherent->getContributedAt()) {
            $this->processor->doContributionAlreadyDone($command);

            return $this->render('renaissance/adherent/contribution/contribution_already_done.html.twig');
        }

        $form = $this
            ->createForm(RevenueType::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $adherent->addRevenueDeclaration($command->revenueAmount);
            $entityManager->flush();

            $bus->dispatch(new RefreshAdherentTagCommand($adherent->getUuid()));

            if (!$command->needContribution()) {
                $this->processor->doNoContributionNeeded($command);

                $contributionRequestHandler->cancelLastContribution($adherent);

                $adherent->setContributionStatus(ContributionStatusEnum::NOT_ELIGIBLE);
                $adherent->setContributedAt(new \DateTime());

                $entityManager->flush();

                return $this->render('renaissance/adherent/contribution/no_contribution_needed.html.twig');
            }

            return $this->redirectToRoute('app_renaissance_contribution_see_amount');
        }

        return $this->render('renaissance/adherent/contribution/fill_revenue.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
