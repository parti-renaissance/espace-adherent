<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\MyCommittee;

use App\Committee\CommitteeMembershipManager;
use App\Committee\CommitteeMembershipTriggerEnum;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Geo\ManagedZoneProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression('is_granted("ABLE_TO_CHANGE_COMMITTEE") or is_granted("IS_IMPERSONATOR")'))]
#[Route(path: '/espace-adherent/mon-comite-local/modifier/{uuid}', name: 'app_my_committee_update', methods: ['GET'])]
class SaveCommitteeUpdateController extends AbstractController
{
    public function __invoke(
        Committee $committee,
        ManagedZoneProvider $zoneProvider,
        CommitteeMembershipManager $committeeMembershipManager,
        EntityManagerInterface $entityManager,
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if ($adherent->isForeignResident() && !$this->isGranted('IS_IMPERSONATOR')) {
            return $this->redirectToRoute('vox_app_redirect');
        }

        $committeeMembershipManager->followCommittee($adherent, $committee, CommitteeMembershipTriggerEnum::MANUAL);

        $this->addFlash('info', 'Votre choix de comité local a bien été sauvegardé');

        return $this->redirectToRoute('app_my_committee_show_current');
    }
}
