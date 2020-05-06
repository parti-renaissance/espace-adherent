<?php

namespace App\Donation;

use MyCLabs\Enum\Enum;

class PayboxPaymentUnsubscriptionErrorEnum extends Enum
{
    public const ERROR_1 = 'Incident technique (Configuration)';
    public const ERROR_2 = 'Données non cohérentes';
    public const ERROR_3 = 'Incident technique (Accès à la base de données)';
    public const ERROR_4 = 'Site inconnu';
    public const ERROR_9 = 'Echec de la résiliation. Aucun abonnement résilié';

    public static function getMessage(int $codeError): string
    {
        if (static::isValidKey($key = "ERROR_$codeError")) {
            return static::$key();
        }

        return 'Error unknown';
    }
}
