<?php

namespace AppBundle\Controller\Api;

use AppBundle\Csv\CsvResponseFactory;
use AppBundle\Entity\Adherent;
use AppBundle\Repository\AdherentRepository;
use League\Csv\Writer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/crm-paris")
 *
 * @Security("is_granted('ROLE_OAUTH_SCOPE_CRM_PARIS')")
 */
class CrmParisController extends Controller
{
    private const CSV_HEADER = [
        'uuid',
        'first_name',
        'last_name',
    ];

    /**
     * @Route("/adherents", name="app_crm_paris_adherents", methods={"GET"})
     */
    public function adherentsAction(
        AdherentRepository $adherentRepository,
        CsvResponseFactory $csvResponseFactory
    ): Response {
        $csv = Writer::createFromPath('php://temp', 'r+');

        $csv->insertOne(self::CSV_HEADER);

        foreach ($adherentRepository->getCrmParisIterator() as $result) {
            /** @var Adherent $adherent */
            $adherent = $result[0];

            $csv->insertOne(array_combine(self::CSV_HEADER, [
                $adherent->getUuid(),
                $adherent->getFirstName(),
                $adherent->getLastName(),
            ]));
        }

        return $csvResponseFactory->createStreamedResponse($csv);
    }
}
