<?php

declare(strict_types=1);

namespace App\NationalEvent\Payment;

use App\Entity\NationalEvent\EventInscription;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\String\UnicodeString;

class RequestParamsBuilder
{
    public function __construct(
        private readonly string $ogonePspId,
        private readonly string $ogoneShaInKey,
    ) {
    }

    public function build(UuidInterface $orderId, int $amount, EventInscription $inscription, string $backUrl): array
    {
        $params = [
            'PSPID' => $this->ogonePspId,
            'ORDERID' => $orderId->toString(),
            'COM' => $inscription->event->getSlug(),
            'COMPLUS' => $inscription->getUuid()->toString(),
            'AMOUNT' => $amount, // en cents
            'CURRENCY' => 'EUR',
            'LANGUAGE' => 'fr_FR',
            'CN' => new UnicodeString($inscription->firstName.' '.$inscription->lastName)->ascii(),
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
