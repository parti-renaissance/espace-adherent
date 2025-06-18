<?php

namespace App\NationalEvent\Payment;

use App\Entity\NationalEvent\EventInscription;
use Ramsey\Uuid\UuidInterface;

class RequestParamsBuilder
{
    public function __construct(
        private readonly string $ogonePspId,
        private readonly string $ogoneShaInKey,
    ) {
    }

    public function build(UuidInterface $orderId, EventInscription $inscription, string $backUrl): array
    {
        $params = [
            'PSPID' => $this->ogonePspId,
            'ORDERID' => $orderId->toString(),
            'COMPLUS' => $inscription->getUuid()->toString(),
            'AMOUNT' => $inscription->transportCosts, // en cents
            'CURRENCY' => 'EUR',
            'LANGUAGE' => 'fr_FR',
            'CN' => $inscription->firstName.' '.$inscription->lastName,
            'EMAIL' => $inscription->addressEmail,
            'ACCEPTURL' => $backUrl.'?status=success',
            'DECLINEURL' => $backUrl.'?status=error',
            'EXCEPTIONURL' => $backUrl.'?status=error',
            'CANCELURL' => $backUrl.'?status=cancel',
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
