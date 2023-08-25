<?php

namespace App\Controller\Renaissance\ElectedRepresentative\Contribution;

use App\Adherent\AdherentRoleEnum;
use App\ElectedRepresentative\Contribution\ContributionStatusEnum;
use App\Entity\Adherent;
use App\Form\Renaissance\ElectedRepresentative\Contribution\RevenueType;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/espace-elus/cotisation', name: 'app_renaissance_elected_representative_contribution_fill_revenue', methods: ['GET|POST'])]
#[IsGranted(AdherentRoleEnum::ONGOING_ELECTED_REPRESENTATIVE)]
class FillRevenueController extends AbstractContributionController
{
    public function __invoke(
        Request $request,
        ElectedRepresentativeRepository $electedRepresentativeRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $command = $this->getCommand($request);
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if (!$this->processor->canFillRevenue($command)) {
            return $this->redirectToRoute('app_renaissance_homepage');
        }

        $this->processor->doFillRevenue($command);

        if (!$command->isRedeclare() && $adherent->getContributedAt()) {
            $this->processor->doContributionAlreadyDone($command);

            return $this->render('renaissance/elected_representative/contribution/contribution_already_done.html.twig');
        }

        $form = $this
            ->createForm(RevenueType::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $adherent->addRevenueDeclaration($command->revenueAmount);
            $entityManager->flush();

            if (!$command->needContribution()) {
                $this->processor->doNoContributionNeeded($command);

                $adherent->setContributionStatus(ContributionStatusEnum::NOT_ELIGIBLE);
                $adherent->setContributedAt(new \DateTime());

                $entityManager->flush();

                return $this->render('renaissance/elected_representative/contribution/no_contribution_needed.html.twig');
            }

            return $this->redirectToRoute('app_renaissance_elected_representative_contribution_see_amount');
        }

        return $this->render('renaissance/elected_representative/contribution/fill_revenue.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
