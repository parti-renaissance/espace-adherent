<?php

namespace App\Renaissance\Donation;

use MyCLabs\Enum\Enum;

class DonationRequestStateEnum extends Enum
{
    public const STATE_DONATION_AMOUNT = 'donation_amount';
    public const STATE_PERSONAL_INFO = 'personal_info';
    public const STATE_TERMS_AND_CONDITIONS = 'terms_and_conditions';
    public const STATE_DONATION_PAYMENT = 'donation_payment';
    public const STATE_FINISH = 'finish';

    public const TO_CHOOSE_DONATION_AMOUNT = 'to_choose_donation_amount';
    public const TO_FILL_PERSONAL_INFO = 'to_fill_personal_info';
    public const TO_ACCEPT_TERMS_AND_CONDITIONS = 'to_accept_terms_and_conditions';
    public const TO_PAY_DONATION = 'to_pay_donation';
    public const TO_FINISH = 'to_finish';
}
