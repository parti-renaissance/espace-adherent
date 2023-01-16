<?php

namespace App\ElectedRepresentative\Contribution;

use MyCLabs\Enum\Enum;

class ContributionRequestStateEnum extends Enum
{
    public const STATE_START = 'start';
    public const STATE_FILL_REVENUE = 'fill_revenue';
    public const STATE_SEE_CONTRIBUTION_AMOUNT = 'see_contribution_amount';
    public const STATE_FILL_CONTRIBUTION_INFORMATIONS = 'fill_contribution_informations';
    public const STATE_FINISH = 'finish';

    public const TO_FILL_REVENUE = 'to_fill_revenue';
    public const TO_SEE_CONTRIBUTION_AMOUNT = 'to_see_contribution_amount';
    public const TO_FILL_CONTRIBUTION_INFORMATIONS = 'to_fill_contribution_informations';
    public const TO_FINISH = 'to_finish';
}
