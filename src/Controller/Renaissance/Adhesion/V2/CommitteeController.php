<?php

namespace App\Controller\Renaissance\Adhesion\V2;

use App\Adhesion\AdhesionStepEnum;
use App\Committee\CommitteeMembershipManager;
use App\Committee\CommitteeMembershipTriggerEnum;
use App\Entity\Adherent;
use App\Repository\CommitteeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/adhesion/comite-local', name: self::ROUTE_NAME, methods: ['GET', 'POST'])]
class CommitteeController extends AbstractController
{
    public const ROUTE_NAME = 'app_adhesion_committee';

    public function __construct(
        private readonly CommitteeMembershipManager $committeeMembershipManager,
        private readonly CommitteeRepository $committeeRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $adherent = $this->getUser();
        if (!$adherent instanceof Adherent) {
            return $this->redirectToRoute(AdhesionController::ROUTE_NAME);
        }

        if ($adherent->hasFinishedAdhesionStep(AdhesionStepEnum::COMMITTEE)) {
            return $this->redirectToRoute('app_renaissance_adherent_space');
        }

        $committees = $this->committeeRepository->findInAdherentZone($adherent);

        if (!$defaultCommittee = $this->committeeMembershipManager->findCommitteeByAddress($adherent->getPostAddress())) {
            $defaultCommittee = $committees[0] ?? null;
        }

        if (!$defaultCommittee) {
            return $this->redirectToRoute('app_adhesion_communication_reminder');
        }

        if ($request->isMethod(Request::METHOD_POST)) {
            if (!$selectedCommittee = $this->committeeRepository->findOneByUuid($request->request->get('committee'))) {
                $this->addFlash('error', "Le comité local sélectionné n'existe pas.");

                return $this->redirectToRoute(self::ROUTE_NAME);
            }

            $this->committeeMembershipManager->followCommittee(
                $adherent,
                $selectedCommittee,
                CommitteeMembershipTriggerEnum::MANUAL
            );
            $adherent->finishAdhesionStep(AdhesionStepEnum::COMMITTEE);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_adhesion_communication_reminder');
        }

        return $this->renderForm('renaissance/adhesion/committee.html.twig', [
            'committees' => $committees,
            'default_committee' => $defaultCommittee,
        ]);
    }
}
