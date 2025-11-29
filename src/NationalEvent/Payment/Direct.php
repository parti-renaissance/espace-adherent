<?php

declare(strict_types=1);

namespace App\NationalEvent\Payment;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class Direct
{
    public function __construct(
        private readonly HttpClientInterface $ogoneClient,
        private readonly string $ogonePspId,
        private readonly string $ogoneUserId,
        private readonly string $ogoneUserPwd,
    ) {
    }

    public function getStatus(string $orderId): string
    {
        return $this->ogoneClient->request('POST', 'querydirect.asp', [
            'body' => [
                'PSPID' => $this->ogonePspId,
                'USERID' => $this->ogoneUserId,
                'PSWD' => $this->ogoneUserPwd,
                'ORDERID' => $orderId,
            ],
        ])->getContent(false);
    }
}
