<?php

namespace App\Controller\EnMarche;

use App\Entity\Adherent;
use App\Repository\CommitteeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('ROLE_SUPERVISOR') or is_granted('ROLE_HOST')"))]
#[Route(path: '/espace-comite', name: 'app_committee_space_dashboard')]
class CommitteeSpaceController extends AbstractController
{
    public function __invoke(CommitteeRepository $repository): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        return $this->render('committee_manager/dashboard.html.twig', [
            'committees' => $repository->findCommitteesForHost($adherent),
        ]);
    }
}
