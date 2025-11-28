<?php

declare(strict_types=1);

namespace App\Donation\Paybox;

use App\Donation\Request\DonationRequestUtils;
use App\Entity\Donation;
use Cocur\Slugify\SlugifyInterface;
use League\ISO3166\ISO3166;
use Lexik\Bundle\PayboxBundle\Paybox\System\Base\Request as LexikRequestHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PayboxFormFactory
{
    public function __construct(
        private readonly string $environment,
        private readonly LexikRequestHandler $requestHandler,
        private readonly LexikRequestHandler $membershipRequestHandler,
        private readonly UrlGeneratorInterface $router,
        private readonly DonationRequestUtils $donationRequestUtils,
        private readonly SlugifyInterface $slugify,
    ) {
    }

    public function createPayboxFormForDonation(Donation $donation, string $callbackRouteName): LexikRequestHandler
    {
        $callbackUrl = $this->router->generate(
            $callbackRouteName,
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
            'PBX_SHOPPINGCART' => $this->buildCartParameter(),
            'PBX_BILLING' => $this->buildBillingParameter($donation),
        ];

        if (str_starts_with($this->environment, 'test')) {
            $parameters['PBX_REPONDRE_A'] = 'https://httpbin.org/status/200';
        }

        if ($donation->isMembership()) {
            return $this->membershipRequestHandler->setParameters($parameters);
        }

        return $this->requestHandler->setParameters($parameters);
    }

    private function buildCartParameter(): string
    {
        return '<?xml version="1.0"?><shoppingcart><total><totalQuantity>1</totalQuantity></total></shoppingcart>';
    }

    private function buildBillingParameter(Donation $donation): string
    {
        $donator = $donation->getDonator();

        return \sprintf(
            '<?xml version="1.0"?><Billing><Address><FirstName>%s</FirstName><LastName>%s</LastName><Address1>%s</Address1><ZipCode>%s</ZipCode><City>%s</City><CountryCode>%s</CountryCode></Address></Billing>',
            $donator->getFirstName() ? $this->slugify($donator->getFirstName(), 22) : '',
            $donator->getLastName() ? $this->slugify($donator->getLastName(), 22) : '',
            $donation->getAddress() ? $this->slugify($donation->getAddress(), 50) : '',
            $donation->getPostalCode() ? $this->slugify($donation->getPostalCode(), 16) : '',
            $donation->getCityName() ? $this->slugify($donation->getCityName(), 50) : '',
            $this->getCountryISO3166Numeric($donation->getCountry() ?? 'FR')
        );
    }

    private function slugify(string $string, ?int $maxLength = null, string $separator = ' '): string
    {
        $slug = $this->slugify->slugify($string, ['separator' => $separator]);

        if ($maxLength) {
            $slug = mb_substr($slug, 0, $maxLength);
        }

        return $slug;
    }

    private function getCountryISO3166Numeric(string $countryCode): int
    {
        $data = (new ISO3166())->alpha2($countryCode);

        return $data['numeric'] ? (int) $data['numeric'] : 250;
    }
}
