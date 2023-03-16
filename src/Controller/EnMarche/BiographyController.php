<?php

namespace App\Controller\EnMarche;

use App\Entity\Biography\ExecutiveOfficeMember;
use App\Repository\Biography\ExecutiveOfficeMemberRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BiographyController extends AbstractController
{
    #[Route(path: '/le-mouvement/notre-organisation', name: 'app_our_organization', methods: ['GET'])]
    public function executiveOfficeMemberListAction(ExecutiveOfficeMemberRepository $repository): Response
    {
        $allMembers = $repository->findAllPublishedMembers();

        return $this->render('page/le-mouvement/notre-organisation.html.twig', [
            'executiveOfficeMembers' => $allMembers->getExecutiveOfficeMembers(),
            'executiveOfficer' => $allMembers->getExecutiveOfficer(),
            'deputyGeneralDelegate' => $allMembers->getDeputyGeneralDelegate(),
        ]);
    }

    /**
     * @Entity("executiveOfficeMember", expr="repository.findOnePublishedBySlug(slug)")
     */
    #[Route(path: '/le-mouvement/notre-organisation/{slug}', name: 'app_our_organization_show', methods: ['GET'])]
    public function executiveOfficeMemberAction(ExecutiveOfficeMember $executiveOfficeMember): Response
    {
        return $this->render('biography/executive_office_member/show.html.twig', [
            'executiveOfficeMember' => $executiveOfficeMember,
        ]);
    }
}
