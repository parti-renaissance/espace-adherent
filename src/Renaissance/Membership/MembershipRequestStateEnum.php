<?php

namespace App\Renaissance\Membership;

use MyCLabs\Enum\Enum;

class MembershipRequestStateEnum extends Enum
{
    public const STATE_START = 'start';
    public const STATE_PERSONAL_INFO = 'personal_info';
    public const STATE_ADHESION_AMOUNT = 'adhesion_amount';
    public const STATE_TERMS_AND_CONDITIONS = 'terms_and_conditions';
    public const STATE_SUMMARY = 'summary';
    public const STATE_ADHESION_PAYMENT = 'adhesion_payment';
    public const STATE_FINISH = 'finish';

    public const TO_FILL_PERSONAL_INFO = 'to_fill_personal_info';
    public const TO_CHOOSE_ADHESION_AMOUNT = 'to_choose_adhesion_amount';
    public const TO_ACCEPT_TERMS_AND_CONDITIONS = 'to_accept_terms_and_conditions';
    public const TO_VALID_SUMMARY = 'to_valid_summary';
    public const TO_PAY_MEMBERSHIP = 'to_pay_membership';
    public const TO_FINISH = 'to_finish';
}
