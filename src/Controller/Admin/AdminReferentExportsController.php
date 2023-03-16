<?php

namespace App\Controller\Admin;

use App\Entity\Referent;
use App\Exporter\ReferentPersonLinkExport;
use App\Repository\ReferentOrganizationalChart\ReferentPersonLinkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/referent/exports')]
class AdminReferentExportsController extends AbstractController
{
    #[Route(path: '/{id}/equipe-departementale', name: 'app_admin_referent_exports_departemental_team', methods: ['GET'])]
    public function exportDepartementaleTeam(
        ReferentPersonLinkExport $referentPersonLinkExport,
        ReferentPersonLinkRepository $referentPersonLinkRepository,
        Referent $referent
    ): Response {
        return $referentPersonLinkExport->createResponse(
            $referentPersonLinkExport->exportToXlsx(
                $referentPersonLinkRepository->findByReferentOrdered($referent)
            )
        );
    }
}
