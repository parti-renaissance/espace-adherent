<?php

namespace App\Controller\Renaissance\MyCommittee;

use App\Committee\CommitteeMembershipManager;
use App\Committee\CommitteeMembershipTriggerEnum;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
use App\Geo\ManagedZoneProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route(path: '/espace-adherent/mon-comite-local/modifier/{uuid}', name: 'app_my_committee_update', methods: ['GET'])]
class SaveCommitteeUpdateController extends AbstractController
{
    public function __invoke(
        Committee $committee,
        ManagedZoneProvider $zoneProvider,
        CommitteeMembershipManager $committeeMembershipManager,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if (!$adherent->isRenaissanceUser() || $adherent->isForeignResident()) {
            return $this->redirect($this->generateUrl('app_renaissance_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL));
        }

        $zones = $adherent->getParentZonesOfType(Zone::DEPARTMENT);

        foreach ($committee->getZones() as $zone) {
            if (!$zoneProvider->zoneBelongsToSomeZones($zone, $zones)) {
                $this->addFlash('error', 'Comité local ne semble pas être dans la même zone que vous');

                return $this->redirectToRoute('app_my_committee_show_list');
            }
        }

        $committeeMembershipManager->followCommittee($adherent, $committee, CommitteeMembershipTriggerEnum::MANUAL);

        $this->addFlash('info', 'Votre choix de comité local a bien été sauvegardé');

        return $this->redirectToRoute('app_my_committee_show_current');
    }
}
