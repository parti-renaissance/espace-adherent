<?php

namespace App\Controller\Admin;

use App\Exporter\ReferentPersonLinkExport;
use App\Repository\ReferentOrganizationalChart\ReferentPersonLinkRepository;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\Response;

class AdminReferentController extends CRUDController
{
    private $referentPersonLinkExport;
    private $repository;

    public function __construct(
        ReferentPersonLinkExport $referentPersonLinkExport,
        ReferentPersonLinkRepository $repository
    ) {
        $this->referentPersonLinkExport = $referentPersonLinkExport;
        $this->repository = $repository;
    }

    public function batchActionExportTeams(ProxyQueryInterface $selectedModelQuery): Response
    {
        return $this->referentPersonLinkExport->createResponse(
            $this->referentPersonLinkExport->exportToXlsx(
                $this->repository->findTeamsOrdered($selectedModelQuery->execute())
            )
        );
    }
}
