<?php

namespace App\Controller\Renaissance\MyCommittee;

use App\Entity\Adherent;
use App\Repository\CommitteeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route(path: '/espace-adherent/mon-comite-local/modifier', name: 'app_my_committee_show_list', methods: ['GET'])]
#[Security('is_granted("ABLE_TO_CHANGE_COMMITTEE") or is_granted("ROLE_PREVIOUS_ADMIN")')]
class ShowCommitteeListController extends AbstractController
{
    public function __invoke(CommitteeRepository $committeeRepository): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if ($adherent->isForeignResident() && !$this->isGranted('ROLE_PREVIOUS_ADMIN')) {
            return $this->redirect($this->generateUrl('app_renaissance_adherent_space', [], UrlGeneratorInterface::ABSOLUTE_URL));
        }

        return $this->render('renaissance/adherent/my_committee/show_committees_list.html.twig', [
            'committees' => $committeeRepository->findInAdherentZone($adherent),
        ]);
    }
}
