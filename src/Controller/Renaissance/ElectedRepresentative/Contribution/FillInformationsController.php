<?php

namespace App\Controller\Renaissance\ElectedRepresentative\Contribution;

use App\Form\Renaissance\ElectedRepresentative\Contribution\InformationsType;
use App\Membership\MembershipNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

/**
 * @Route(path="/espace-elus/cotisation/informations", name="app_renaissance_elected_representative_contribution_fill_informations", methods={"GET|POST"})
 */
class FillInformationsController extends AbstractContributionController
{
    public function __invoke(
        Request $request,
        EntityManagerInterface $entityManager,
        MembershipNotifier $notifier,
        EncoderFactoryInterface $encoders
    ): Response {
        $this->checkContributionsEnabled();

        $command = $this->getCommand();

        if (!$this->processor->canFillContributionInformations($command)) {
            return $this->redirectToRoute('app_renaissance_elected_representative_contribution_see_amount');
        }

        $this->processor->doFillContributionInformations($command);

        $form = $this
            ->createForm(InformationsType::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            // handle contribution

            $this->processor->doCompleteContributionRequest($command);

            return $this->render('renaissance/elected_representative/contribution/confirmation.html.twig');
        }

        return $this->render('renaissance/elected_representative/contribution/fill_informations.html.twig', [
            'form' => $form->createView(),
            'command' => $command,
        ]);
    }
}
