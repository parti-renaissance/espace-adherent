<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Donation;
use Lexik\Bundle\PayboxBundle\Paybox\System\Base\Request as LexikRequestHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PayboxFormFactory
{
    private $environment;
    private $requestHandler;
    private $urlGenerator;
    private $donationRequestUtils;

    public function __construct(
        string $environment,
        LexikRequestHandler $requestHandler,
        UrlGeneratorInterface $urlGenerator,
        DonationRequestUtils $donationRequestUtils
    ) {
        $this->environment = $environment;
        $this->requestHandler = $requestHandler;
        $this->urlGenerator = $urlGenerator;
        $this->donationRequestUtils = $donationRequestUtils;
    }

    public function createPayboxFormForDonation(Donation $donation): LexikRequestHandler
    {
        $callbackUrl = $this->urlGenerator->generate(
            'donation_callback',
            $this->donationRequestUtils->buildCallbackParameters(),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $parameters = [
            'PBX_CMD' => $donation->getPayboxOrderRefWithSuffix(),
            'PBX_PORTEUR' => $donation->getEmailAddress(),
            'PBX_TOTAL' => $donation->getAmount(),
            'PBX_DEVISE' => '978',
            'PBX_RETOUR' => 'id:R;authorization:A;result:E;transaction:S;amount:M;date:W;time:Q;card_type:C;card_end:D;card_print:H;subscription:B',
            'PBX_TYPEPAIEMENT' => 'CARTE',
            'PBX_TYPECARTE' => 'CB',
            'PBX_RUF1' => 'POST',
            'PBX_EFFECTUE' => $callbackUrl,
            'PBX_REFUSE' => $callbackUrl,
            'PBX_ANNULE' => $callbackUrl,
            'PBX_REPONDRE_A' => $this->urlGenerator->generate('lexik_paybox_ipn', ['time' => time()], UrlGeneratorInterface::ABSOLUTE_URL),
        ];

        if (0 === strpos($this->environment, 'test')) {
            $parameters['PBX_REPONDRE_A'] = 'https://httpbin.org/status/200';
        }

        return $this->requestHandler->setParameters($parameters);
    }
}
