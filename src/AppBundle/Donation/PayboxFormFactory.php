<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Donation;
use Lexik\Bundle\PayboxBundle\Paybox\System\Base\Request as LexikRequestHandler;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PayboxFormFactory
{
    private $environment;
    private $requestHandler;
    private $router;

    public function __construct(string $environment, LexikRequestHandler $requestHandler, Router $router)
    {
        $this->environment = $environment;
        $this->requestHandler = $requestHandler;
        $this->router = $router;
    }

    public function createPayboxFormForDonation(Donation $donation)
    {
        $parameters = [
            'PBX_CMD' => $donation->getUuid()->toString(),
            'PBX_PORTEUR' => $donation->getEmailAddress(),
            'PBX_TOTAL' => $donation->getAmount() * 100,
            'PBX_DEVISE' => '978',
            'PBX_RETOUR' => 'id:R;authorization:A;result:E;transaction:S;amount:M;date:W;time:Q;card_type:C;card_end:D;card_print:H',
            'PBX_TYPEPAIEMENT' => 'CARTE',
            'PBX_TYPECARTE' => 'CB',
            'PBX_RUF1' => 'POST',
            'PBX_EFFECTUE' => $this->router->generate('donation_callback', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'PBX_REFUSE' => $this->router->generate('donation_callback', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'PBX_ANNULE' => $this->router->generate('donation_callback', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'PBX_REPONDRE_A' => $this->router->generate('lexik_paybox_ipn', ['time' => time()], UrlGeneratorInterface::ABSOLUTE_URL),
        ];

        if ('test_sqlite' === $this->environment || 'test_mysql' === $this->environment) {
            $parameters = array_merge($parameters, [
                'PBX_EFFECTUE' => 'https://httpbin.org/status/200',
                'PBX_REFUSE' => 'https://httpbin.org/status/200',
                'PBX_ANNULE' => 'https://httpbin.org/status/200',
                'PBX_REPONDRE_A' => 'https://httpbin.org/status/200',
            ]);
        }

        $this->requestHandler->setParameters($parameters);

        return $this->requestHandler;
    }
}
