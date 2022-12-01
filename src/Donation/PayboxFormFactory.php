<?php

namespace App\Donation;

use App\Entity\Donation;
use Lexik\Bundle\PayboxBundle\Paybox\System\Base\Request as LexikRequestHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PayboxFormFactory
{
    public function __construct(
        private readonly string $environment,
        private readonly LexikRequestHandler $requestHandler,
        private readonly UrlGeneratorInterface $router,
        private readonly DonationRequestUtils $donationRequestUtils,
        private readonly string $payboxMembershipSite,
        private readonly string $payboxMembershipIdentifier,
        private readonly string $payboxMembershipKey
    ) {
    }

    public function createPayboxFormForDonation(Donation $donation): LexikRequestHandler
    {
        $callbackUrl = $this->router->generate(
            $donation->isMembership() ? 'app_renaissance_adhesion_callback' : 'app_renaissance_donation_callback',
            $this->donationRequestUtils->buildCallbackParameters(),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $parameters = [
            'PBX_CMD' => $donation->getPayboxOrderRefWithSuffix(),
            'PBX_PORTEUR' => $donation->getDonator()->getEmailAddress(),
            'PBX_TOTAL' => $donation->getAmount(),
            'PBX_DEVISE' => '978',
            'PBX_RETOUR' => 'id:R;authorization:A;result:E;transaction:S;amount:M;date:W;time:Q;card_type:C;card_end:D;card_print:H;subscription:B',
            'PBX_TYPEPAIEMENT' => 'CARTE',
            'PBX_TYPECARTE' => 'CB',
            'PBX_RUF1' => 'POST',
            'PBX_EFFECTUE' => $callbackUrl,
            'PBX_REFUSE' => $callbackUrl,
            'PBX_ANNULE' => $callbackUrl,
            'PBX_REPONDRE_A' => $this->router->generate('lexik_paybox_ipn', ['time' => time()], UrlGeneratorInterface::ABSOLUTE_URL),
        ];

        if (str_starts_with($this->environment, 'test')) {
            $parameters['PBX_REPONDRE_A'] = 'https://httpbin.org/status/200';
        }

        if ($donation->isMembership()) {
            $parameters['PBX_SITE'] = $this->payboxMembershipSite;
            $parameters['PBX_IDENTIFIANT'] = $this->payboxMembershipIdentifier;
            $parameters['PBX_HMAC'] = $this->payboxMembershipKey;
        }

        return $this->requestHandler->setParameters($parameters);
    }
}
