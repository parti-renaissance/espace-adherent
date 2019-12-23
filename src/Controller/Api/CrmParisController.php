<?php

namespace AppBundle\Controller\Api;

use AppBundle\Csv\CsvResponseFactory;
use AppBundle\Entity\Adherent;
use AppBundle\Repository\AdherentRepository;
use League\Csv\CharsetConverter;
use League\Csv\Writer;
use libphonenumber\PhoneNumberUtil;
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
    ];

    /**
     * @Route("/adherents", name="app_crm_paris_adherents", methods={"GET"})
     */
    public function adherentsAction(
        AdherentRepository $adherentRepository,
        CsvResponseFactory $csvResponseFactory,
        PhoneNumberUtil $phoneNumberUtil
    ): Response {
        set_time_limit(0);

        $csv = Writer::createFromPath('php://temp', 'r+');
        $csv->setDelimiter(self::CSV_DELIMITER);
        $csv->addFormatter((new CharsetConverter())->outputEncoding(self::CSV_OUTPUT_ENCODING));

        $csv->insertOne(self::CSV_HEADER);
        foreach ($adherentRepository->getCrmParisIterator() as $result) {
            /** @var Adherent $adherent */
            $adherent = $result[0];

            $csv->insertOne(array_combine(self::CSV_HEADER, [
                $adherent->getUuid(),
                $adherent->getFirstName(),
                $adherent->getLastName(),
                $adherent->getEmailAddress(),
                $adherent->getPhone() ? $phoneNumberUtil->format($adherent->getPhone(), 'NATIONAL') : null,
                $adherent->getAddress(),
                $adherent->getPostalCode(),
                $adherent->getCityName(),
                5 === mb_strlen($adherent->getPostalCode()) ? mb_substr($adherent->getPostalCode(), 4, 2) : null,
                $adherent->getGender(),
                $adherent->getBirthdate() ? $adherent->getBirthdate()->format('d-m-Y') : null,
                $adherent->getLatitude(),
                $adherent->getLongitude(),
                implode(', ', $adherent->getInterests()),
            ]));
        }

        return $csvResponseFactory->createStreamedResponse($csv);
    }
}
