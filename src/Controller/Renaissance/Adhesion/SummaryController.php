<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Entity\Adherent;
use App\Entity\Renaissance\Adhesion\AdherentRequest;
use App\Form\Renaissance\Adhesion\AdhesionConfirmationType;
use App\Membership\MembershipNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

#[Route(path: '/v1/adhesion/recapitulatif', name: 'app_renaissance_adhesion_summary', methods: ['GET|POST'])]
class SummaryController extends AbstractAdhesionController
{
    public function __invoke(
        Request $request,
        EntityManagerInterface $entityManager,
        MembershipNotifier $notifier,
        EncoderFactoryInterface $encoders
    ): Response {
        $command = $this->getCommand();

        if (!$this->processor->canValidSummary($command)) {
            return $this->redirectToRoute('app_renaissance_adhesion_amount');
        }

        $this->processor->doValidSummary($command);

        $form = $this
            ->createForm(AdhesionConfirmationType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($adherentRequest = AdherentRequest::create(
                $command,
                $command->password ? $encoders->getEncoder(Adherent::class)->encodePassword($command->password, null) : null
            ));
            $entityManager->flush();

            $notifier->sendRenaissanceValidationEmail($adherentRequest);

            $this->storage->clear();

            return $this->render('renaissance/adhesion/confirmation.html.twig');
        }

        return $this->render('renaissance/adhesion/summary.html.twig', [
            'form' => $form->createView(),
            'command' => $command,
        ]);
    }
}
