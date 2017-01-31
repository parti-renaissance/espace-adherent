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

    public function handle(string $uuid, Request $request): Response
    {
        /** @var Donation $donation */
        $donation = $this->entityManager->getRepository(Donation::class)->findOneByUuid($uuid);

        if (!$donation) {
            return new RedirectResponse($this->router->generate('donation_index'));
        }

        if (!$donation->isFinished()) {
            $donation->finish($this->extractPayboxPayloadFromRequest($request));

            $this->entityManager->persist($donation);
            $this->entityManager->flush();
        }

        return $this->createRedirectResponseForDonation($donation);
    }

    private function extractPayboxPayloadFromRequest(Request $request): array
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
                'uuid' => $donation->getUuid()->toString(),
                'status' => 'effectue',
            ]));
        }

        // Known error
        if (isset(self::$errorCodes[$code])) {
            return new RedirectResponse($this->router->generate('donation_result', [
                'uuid' => $donation->getUuid()->toString(),
                'status' => 'erreur',
                'code' => self::$errorCodes[$code],
            ]));
        }

        // Unknown error
        return new RedirectResponse($this->router->generate('donation_result', [
            'uuid' => $donation->getUuid()->toString(),
            'status' => 'erreur',
            'code' => 'unknown',
        ]));
    }
}
