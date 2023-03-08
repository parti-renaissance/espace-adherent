<?php

namespace App\Controller\Renaissance\MyCommittee;

use App\Committee\CommitteeManager;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\Geo\Zone;
use App\Geo\ManagedZoneProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-adherent/mon-comite-local/modifier/{uuid}", name="app_my_committee_update", methods={"GET"})
 */
class SaveCommitteeUpdateController extends AbstractController
{
    public function __invoke(
        Committee $committee,
        ManagedZoneProvider $zoneProvider,
        CommitteeManager $committeeManager,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if ($adherent->isFrench()) {
            $zones = $adherent->getParentZonesOfType(Zone::DEPARTMENT);
        } else {
            $zones = $adherent->getZonesOfType(Zone::COUNTRY);
        }

        foreach ($committee->getZones() as $zone) {
            if (!$zoneProvider->zoneBelongsToSomeZones($zone, $zones)) {
                $this->addFlash('error', 'Comité local ne semble pas être dans la même zone que vous');

                return $this->redirectToRoute('app_my_committee_show_list');
            }
        }

        $oldMemberships = $adherent->getMemberships()->getCommitteeV2Memberships();
        array_walk(
            $oldMemberships,
            fn (CommitteeMembership $membership) => $committeeManager->unfollowCommittee($adherent, $membership->getCommittee()),
        );

        $entityManager->persist($committeeMembership = $adherent->followCommittee($committee));
        $committeeMembership->setTrigger('manual');

        $entityManager->flush();

        $this->addFlash('info', 'Votre choix de comité local a bien été sauvegardé');

        return $this->redirectToRoute('app_my_committee_show_current');
    }
}
