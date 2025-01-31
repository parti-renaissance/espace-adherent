<?php

namespace App\Controller\EnMarche;

use App\Referent\ManagedCommitteesExporter;
use App\Repository\CommitteeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('ROLE_DEPUTY') or (is_granted('ROLE_DELEGATED_DEPUTY') and is_granted('HAS_DELEGATED_ACCESS_COMMITTEE'))"))]
#[Route(path: '/espace-depute', name: 'app_deputy_')]
class DeputyController extends AbstractController
{
    use AccessDelegatorTrait;

    public function getSpaceType(): string
    {
        return 'deputy';
    }

    #[Route(path: '/comites', name: 'committees', methods: ['GET'])]
    public function listCommitteesAction(
        Request $request,
        CommitteeRepository $committeeRepository,
        ManagedCommitteesExporter $committeesExporter,
    ): Response {
        return $this->render('deputy/committees_list.html.twig', [
            'managedCommitteesJson' => $committeesExporter->exportAsJson(
                $committeeRepository->findInZones([$this->getMainUser($request->getSession())->getDeputyZone()])
            ),
        ]);
    }
}
