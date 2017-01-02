<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Donation;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TransactionCallbackHandler
{
    private $router;
    private $entityManager;

    private static $errorCodes = [
        // Platform or authorization center error
        '00001' => 'paybox',
        '00003' => 'paybox',

        // Invalid card number/validity
        '00004' => 'invalid-card',
        '00008' => 'invalid-card',
        '00021' => 'invalid-card',

        // Timeout
        '00030' => 'timeout',
    ];

    public function __construct(Router $router, EntityManager $entityManager)
    {
        $this->router = $router;
        $this->entityManager = $entityManager;
    }

    public function handle(string $id, Request $request): Response
    {
        $donation = $this->entityManager->find('AppBundle:Donation', $id);

        if (!$donation) {
            return new RedirectResponse($this->router->generate('donation_index'));
        }

        if (!$donation->isFinished()) {
            $this->populateDonationWithRequestData($donation, $request);
            $donation->setFinished(true);
        }

        return $this->createRedirectResponseForDonation($donation);
    }

    private function populateDonationWithRequestData(Donation $donation, Request $request)
    {
        $data = $this->extractRequestData($request);

        $donation->setPayboxResultCode($data['result']);
        $donation->setPayboxAuthorizationCode($data['authorization']);
        $donation->setPayboxPayload($data);
    }

    private function extractRequestData(Request $request): array
    {
        $data = array_merge($request->query->all(), [
            'authorization' => $request->query->get('authorization'),
            'result' => $request->query->get('result'),
        ]);

        if (isset($data['id'])) {
            unset($data['id']);
        }

        if (isset($data['Sign'])) {
            unset($data['Sign']);
        }

        return $data;
    }

    private function createRedirectResponseForDonation(Donation $donation): Response
    {
        $code = $donation->getPayboxResultCode();

        // Success
        if ($code === '00000') {
            return new RedirectResponse($this->router->generate('donation_result', [
                'id' => $donation->getId()->toString(),
                'status' => 'effectue',
            ]));
        }

        // Known error
        if (isset(self::$errorCodes[$code])) {
            return new RedirectResponse($this->router->generate('donation_result', [
                'id' => $donation->getId()->toString(),
                'status' => 'erreur',
                'code' => self::$errorCodes[$code],
            ]));
        }

        // Unknown error
        return new RedirectResponse($this->router->generate('donation_result', [
            'id' => $donation->getId()->toString(),
            'status' => 'erreur',
            'code' => 'unknown',
        ]));
    }
}
