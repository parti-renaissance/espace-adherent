<?php

namespace App\NationalEvent\Payment;

use App\Entity\NationalEvent\EventInscription;

class RequestParamsBuilder
{
    public function __construct(
        private readonly string $ogonePspId,
        private readonly string $ogoneShaInKey,
    ) {
    }

    public function build(EventInscription $inscription, string $backUrl): array
    {
        $params = [
            'PSPID' => $this->ogonePspId,
            'ORDERID' => $inscription->getUuid()->toString(),
            'AMOUNT' => $inscription->transportCosts, // en cents
            'CURRENCY' => 'EUR',
            'LANGUAGE' => 'fr_FR',
            'CN' => $inscription->firstName.' '.$inscription->lastName,
            'EMAIL' => $inscription->addressEmail,
            'BACKURL' => $backUrl,
        ];

        ksort($params);
        $shaString = '';
        foreach ($params as $key => $value) {
            if ('' !== $value && null !== $value) {
                $shaString .= strtoupper($key).'='.$value.$this->ogoneShaInKey;
            }
        }
        $params['SHASIGN'] = strtoupper(hash('sha512', $shaString));

        return $params;
    }
}
