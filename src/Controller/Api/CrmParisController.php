<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Adherent;
use AppBundle\Repository\AdherentRepository;
use League\Csv\Writer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/crm-paris")
 *
 * @Security("is_granted('ROLE_OAUTH_SCOPE_READ:CRM_PARIS')")
 */
class CrmParisController extends Controller
{
    private const FLUSH_THRESHOLD = 1000;

    /**
     * @Route("/adherents", name="app_crm_paris_adherents", methods={"GET"})
     */
    public function adherentsAction(AdherentRepository $adherentRepository): Response
    {
        $csv = Writer::createFromPath('php://temp', 'r+');

        foreach ($adherentRepository->getCrmParisIterator() as $result) {
            /** @var Adherent $adherent */
            $adherent = $result[0];

            $csv->insertOne([
                'uuid' => $adherent->getUuid(),
                'first_name' => $adherent->getFirstName(),
                'last_name' => $adherent->getLastName(),
            ]);
        }

        $content_callback = function () use ($csv) {
            foreach ($csv->chunk(1024) as $offset => $chunk) {
                echo $chunk;
                if (0 === $offset % self::FLUSH_THRESHOLD) {
                    flush();
                }
            }
        };

        $response = new StreamedResponse();
        $response->headers->set('Content-Encoding', 'none');
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('%s-adherents.csv', date('YmdHis'))
        );

        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Description', 'File Transfer');
        $response->setCallback($content_callback);
        $response->send();
    }
}
