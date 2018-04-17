<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use AppBundle\Exporter\ReferentPersonLinkExport;
use AppBundle\Repository\ReferentOrganizationalChart\ReferentPersonLinkRepository;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\Response;

class AdminReferentController extends CRUDController
{
    private $referentPersonLinkExport;

    public function __construct(ReferentPersonLinkExport $referentPersonLinkExport)
    {
        $this->referentPersonLinkExport = $referentPersonLinkExport;
    }

    public function batchActionExportTeams(ProxyQueryInterface $selectedModelQuery): Response
    {
        /** @var ReferentPersonLinkRepository $referentPersonLinkRepository */
        $referentPersonLinkRepository = $this->container->get('doctrine')->getRepository(ReferentPersonLink::class);

        return $this->referentPersonLinkExport->createResponse(
            $this->referentPersonLinkExport->exportToXlsx(
                $referentPersonLinkRepository->findTeamsOrdered($selectedModelQuery->execute())
            )
        );
    }
}
