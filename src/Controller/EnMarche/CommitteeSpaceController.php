<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Repository\CommitteeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/espace-comite", name="app_committee_space_dashboard")
 *
 * @Security("is_granted('ROLE_SUPERVISOR') or is_granted('ROLE_HOST')")
 */
class CommitteeSpaceController extends AbstractController
{
    /**
     * @param UserInterface|Adherent $adherent
     */
    public function __invoke(UserInterface $adherent, CommitteeRepository $repository): Response
    {
        return $this->render('committee_manager/dashboard.html.twig', [
            'committees' => $repository->findCommitteesByPrivilege($adherent, CommitteeMembership::getHostPrivileges()),
        ]);
    }
}
