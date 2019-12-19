<?php

namespace AppBundle\Controller\Api;

use AppBundle\Csv\CsvResponseFactory;
use AppBundle\Entity\Adherent;
use AppBundle\Repository\AdherentRepository;
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
    private const CSV_HEADER = [
        'uuid',
        'first_name',
        'last_name',
        'email_address',
        'phone',
        'address',
        'gender',
        'birthdate',
        'latitude',
        'longitude',
        'interests',
        'subscription_types',
    ];

    /**
     * @Route("/adherents", name="app_crm_paris_adherents", methods={"GET"})
     */
    public function adherentsAction(
        AdherentRepository $adherentRepository,
        CsvResponseFactory $csvResponseFactory,
        PhoneNumberUtil $phoneNumberUtil
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
                $adherent->getEmailAddress(),
                $adherent->getPhone() ? $phoneNumberUtil->format($adherent->getPhone(), 'NATIONAL') : null,
                $adherent->getInlineFormattedAddress(),
                $adherent->getGender(),
                $adherent->getBirthdate() ? $adherent->getBirthdate()->format('Y-m-d') : null,
                $adherent->getLatitude(),
                $adherent->getLongitude(),
                implode(', ', $adherent->getInterests()),
                implode(', ', $adherent->getSubscriptionTypeCodes()),
            ]));
        }

        return $csvResponseFactory->createStreamedResponse($csv);
    }
}
