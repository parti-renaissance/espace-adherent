<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Referent;
use AppBundle\Exporter\ReferentPersonLinkExport;
use AppBundle\Repository\ReferentOrganizationalChart\ReferentPersonLinkRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/referent/exports")
 */
class AdminReferentExportsController extends Controller
{
    /**
     * @Route("/{id}/equipe-departementale", name="app_admin_referent_exports_departemental_team")
     * @Method("GET")
     */
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
