<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Donation;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ResultCodeHandler
{
    private $router;

    private static $errorCodes = [
        // Platform or authorization center error
        '00001' => 'paybox',
        '00003' => 'paybox',

        // Invalid card number/validity
        '00004' => 'invalid-card',
        '00008' => 'invalid-card',

        // Invalid card number/validity
        '00021' => 'unauthorized-card',

        // Timeout
        '00030' => 'timeout',
    ];

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function createRedirectResponseForDonation(Donation $donation = null): RedirectResponse
    {
        if (!$donation) {
            return new RedirectResponse($this->router->generate('donation_index'));
        }

        if (!$donation->isFinished()) {
            return new RedirectResponse($this->router->generate('donation_pay', [
                'id' => $donation->getId()->toString(),
            ]));
        }

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
