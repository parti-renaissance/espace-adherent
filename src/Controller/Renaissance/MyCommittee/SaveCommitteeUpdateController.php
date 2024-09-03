<?php

namespace App\Controller\Renaissance\MyCommittee;

use App\Committee\CommitteeMembershipManager;
use App\Committee\CommitteeMembershipTriggerEnum;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Geo\ManagedZoneProvider;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route(path: '/espace-adherent/mon-comite-local/modifier/{uuid}', name: 'app_my_committee_update', methods: ['GET'])]
#[Security('is_granted("ABLE_TO_CHANGE_COMMITTEE") or is_granted("ROLE_PREVIOUS_ADMIN")')]
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

        if ($adherent->isForeignResident() && !$this->isGranted('ROLE_PREVIOUS_ADMIN')) {
            return $this->redirect($this->generateUrl('app_renaissance_adherent_space', [], UrlGeneratorInterface::ABSOLUTE_URL));
        }

        $committeeMembershipManager->followCommittee($adherent, $committee, CommitteeMembershipTriggerEnum::MANUAL);

        $this->addFlash('info', 'Votre choix de comité local a bien été sauvegardé');

        return $this->redirectToRoute('app_my_committee_show_current');
    }
}
