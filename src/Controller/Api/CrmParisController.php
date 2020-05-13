<?php

namespace App\Controller\Api;

use App\Csv\CsvResponseFactory;
use App\Repository\AdherentRepository;
use League\Csv\CharsetConverter;
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
    private const CSV_DELIMITER = ';';
    private const CSV_OUTPUT_ENCODING = 'windows-1252';
    private const CSV_HEADER = [
        'uuid',
        'first_name',
        'last_name',
        'email_address',
        'phone',
        'address',
        'postal_code',
        'city_name',
        'district',
        'gender',
        'birthdate',
        'latitude',
        'longitude',
        'interests',
        'sms_mms',
    ];

    /**
     * @Route("/adherents", name="app_crm_paris_adherents", methods={"GET"})
     */
    public function adherentsAction(
        AdherentRepository $adherentRepository,
        CsvResponseFactory $csvResponseFactory
    ): Response {
        set_time_limit(0);

        $csv = Writer::createFromPath('php://temp', 'r+');
        $csv->setDelimiter(self::CSV_DELIMITER);
        $csv->addFormatter((new CharsetConverter())->outputEncoding(self::CSV_OUTPUT_ENCODING));

        $csv->insertOne(self::CSV_HEADER);
        $csv->insertAll($adherentRepository->getCrmParisRecords());

        return $csvResponseFactory->createStreamedResponse($csv);
    }
}
