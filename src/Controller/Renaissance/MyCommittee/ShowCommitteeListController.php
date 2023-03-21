<?php

namespace App\Controller\Renaissance\MyCommittee;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Repository\CommitteeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/espace-adherent/mon-comite-local/modifier', name: 'app_my_committee_show_list', methods: ['GET'])]
class ShowCommitteeListController extends AbstractController
{
    public function __invoke(CommitteeRepository $committeeRepository): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        return $this->render('renaissance/adherent/my_committee/show_committees_list.html.twig', [
            'committees' => $committeeRepository->findInZones(!$adherent->isForeignResident() ? $adherent->getParentZonesOfType(Zone::DEPARTMENT) : $adherent->getZonesOfType(Zone::COUNTRY)),
        ]);
    }
}
