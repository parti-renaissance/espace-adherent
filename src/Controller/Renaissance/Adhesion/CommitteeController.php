<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Adhesion;

use App\Adhesion\AdhesionStepEnum;
use App\Committee\CommitteeManager;
use App\Committee\CommitteeMembershipManager;
use App\Committee\CommitteeMembershipTriggerEnum;
use App\Entity\Adherent;
use App\Repository\CommitteeRepository;
use App\Utils\UtmParams;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/adhesion/comite-local', name: self::ROUTE_NAME, methods: ['GET', 'POST'])]
class CommitteeController extends AbstractController
{
    public const ROUTE_NAME = 'app_adhesion_committee';

    public function __construct(
        private readonly CommitteeMembershipManager $committeeMembershipManager,
        private readonly CommitteeManager $committeeManager,
        private readonly CommitteeRepository $committeeRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $utmParams = UtmParams::fromRequest($request);

        $adherent = $this->getUser();
        if (!$adherent instanceof Adherent) {
            return $this->redirectToRoute(AdhesionController::ROUTE_NAME, $utmParams);
        }

        if (!$adherent->isRenaissanceAdherent()) {
            $adherent->finishAdhesionStep(AdhesionStepEnum::COMMITTEE);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_adhesion_finish', $utmParams);
        }

        if ($adherent->hasFinishedAdhesionStep(AdhesionStepEnum::COMMITTEE)) {
            return $this->redirectToRoute('vox_app_redirect');
        }

        $committees = $this->committeeRepository->findInAdherentZone($adherent);

        if ($defaultCommittee = $this->committeeManager->findCommitteeByAddress($adherent->getPostAddress())) {
            $committees = array_unique(array_merge($committees, [$defaultCommittee]));
        } else {
            $defaultCommittee = $committees[0] ?? null;
        }

        if (!$defaultCommittee) {
            $adherent->finishAdhesionStep(AdhesionStepEnum::COMMITTEE);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_adhesion_finish', $utmParams);
        }

        if ($request->isMethod(Request::METHOD_POST)) {
            if (!$selectedCommittee = $this->committeeRepository->findOneByUuid($request->request->get('committee'))) {
                $this->addFlash('error', "Le comité local sélectionné n'existe pas.");

                return $this->redirectToRoute(self::ROUTE_NAME, $utmParams);
            }

            $this->committeeMembershipManager->followCommittee(
                $adherent,
                $selectedCommittee,
                CommitteeMembershipTriggerEnum::MANUAL
            );
            $adherent->finishAdhesionStep(AdhesionStepEnum::COMMITTEE);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_adhesion_finish', $utmParams);
        }

        return $this->render('renaissance/adhesion/committee.html.twig', [
            'committees' => $committees,
            'default_committee' => $defaultCommittee,
        ]);
    }
}
