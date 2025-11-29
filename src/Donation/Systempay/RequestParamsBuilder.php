<?php

declare(strict_types=1);

namespace App\Donation\Systempay;

use App\BesoinDEurope\Donation\DonationRequest;
use App\ValueObject\Genders;

class RequestParamsBuilder
{
    public function __construct(
        private readonly string $systemPaySiteId,
        private readonly string $systemPayMode,
        private readonly string $systemPayKey,
    ) {
    }

    public function build(DonationRequest $donationRequest): array
    {
        $params = [
            'vads_site_id' => $this->systemPaySiteId,
            'vads_ctx_mode' => $this->systemPayMode,
            'vads_trans_id' => \sprintf("%'.06d", (strtotime('tomorrow') - time()) * 10 + ((microtime(true) * 10) % 10)),
            'vads_trans_date' => (new \DateTime(timezone: new \DateTimeZone('UTC')))->format('YmdHis'),
            'vads_amount' => $donationRequest->amount * 100,
            'vads_currency' => '978',
            'vads_action_mode' => 'INTERACTIVE',
            'vads_page_action' => 'PAYMENT',
            'vads_version' => 'V2',
            'vads_payment_config' => 'SINGLE',
            'vads_capture_delay' => '0',
            'vads_validation_mode' => '0',
            'vads_cust_title' => match ($donationRequest->civility) {
                Genders::MALE => 'Monsieur',
                Genders::FEMALE => 'Madame',
                default => '',
            },
            'vads_cust_first_name' => $donationRequest->firstName,
            'vads_cust_last_name' => $donationRequest->lastName,
            'vads_cust_email' => $donationRequest->email,
            'vads_cust_address' => $donationRequest->address->getAddress(),
            'vads_cust_address2' => $donationRequest->address->getAdditionalAddress(),
            'vads_cust_zip' => $donationRequest->address->getPostalCode(),
            'vads_cust_city' => $donationRequest->address->getCityName(),
            'vads_cust_country' => $donationRequest->address->getCountry(),
            'vads_ext_info_nationalite' => $donationRequest->nationality,
            'vads_ext_info_utm_source' => $donationRequest->utmSource,
            'vads_ext_info_utm_campagne' => $donationRequest->utmCampaign,
        ];
        ksort($params);
        $data = implode('+', $params);
        $data .= '+'.$this->systemPayKey;

        $signData = base64_encode(hash_hmac('sha256', $data, $this->systemPayKey, true));

        $params['signature'] = $signData;

        return $params;
    }
}
