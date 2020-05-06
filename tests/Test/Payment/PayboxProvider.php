<?php

namespace Tests\App\Test\Payment;

use Lexik\Bundle\PayboxBundle\Paybox\System\Tools;

class PayboxProvider
{
    private $privateKey;

    public function __construct(string $privateKey)
    {
        $this->privateKey = $privateKey;
    }

    public function prepareCallbackParameters(string $donationUuid, string $status): array
    {
        $time = date('H:i:s');
        $data = [
            'id' => $donationUuid,
            'authorization' => 'XXXXXX',
            'result' => $status,
            'transaction' => '',
            'amount' => '',
            'date' => date('dmY'),
            'time' => urlencode($time),
            'card_type' => '',
            'card_end' => '',
            'card_print' => '',
            'subscription' => '',
        ];

        $signature = null;
        $privateKey = openssl_pkey_get_private($this->privateKey);
        openssl_sign(Tools::stringify($data), $signature, $privateKey);

        $data['Sign'] = urlencode(base64_encode($signature));
        $data['time'] = $time;

        return $data;
    }

    public function getIpnUri(): string
    {
        return sprintf('/don/payment-ipn/%d', time());
    }
}
