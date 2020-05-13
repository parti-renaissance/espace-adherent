<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PaymentExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [new TwigFunction('get_payment_message_by_code', [$this, 'getPaymentMessageByCode'])];
    }

    public function getPaymentMessageByCode(string $code): string
    {
        switch ($code) {
            case '00001':
                return 'payment.message.connection_failed';

            case '00003':
            case '00041':
                return 'payment.message.error_paybox';

            case '00004':
                return 'payment.message.card_number_invalid';

            case '00008':
                return 'payment.message.expiry_date_invalid';

            case '00015':
                return 'payment.message.payment_already_done';

            case '00030':
                return 'payment.message.timeout';

            case '00040':
                return 'payment.message.blocked_by_fraud_filter';

            case '00105':
            case '00134':
            case '00143':
            case '00151':
            case '00157':
            case '00159':
                return 'payment.message.do_not_honor';

            case '00114':
                return 'payment.message.holder_number_invalid';

            case '00115':
                return 'payment.message.card_issuer_invalid';

            case '00154':
                return 'payment.message.expiry_data_passed';

            case '00156':
                return 'payment.message.card_absent';

            default:
                return 'payment.message.generic';
        }
    }
}
