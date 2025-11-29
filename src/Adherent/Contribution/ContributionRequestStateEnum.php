<?php

declare(strict_types=1);

namespace App\Adherent\Contribution;

use MyCLabs\Enum\Enum;

class ContributionRequestStateEnum extends Enum
{
    public const STATE_START = 'start';
    public const STATE_FILL_REVENUE = 'fill_revenue';
    public const STATE_NO_CONTRIBUTION_NEEDED = 'no_contribution_needed';
    public const STATE_CONTRIBUTION_ALREADY_DONE = 'contribution_already_done';
    public const STATE_SEE_CONTRIBUTION_AMOUNT = 'see_contribution_amount';
    public const STATE_FILL_CONTRIBUTION_INFORMATIONS = 'fill_contribution_informations';
    public const STATE_CONTRIBUTION_COMPLETE = 'contribution_complete';

    public const TO_FILL_REVENUE = 'to_fill_revenue';
    public const TO_NO_CONTRIBUTION_NEEDED = 'to_no_contribution_needed';
    public const TO_CONTRIBUTION_ALREADY_DONE = 'to_contribution_already_done';
    public const TO_SEE_CONTRIBUTION_AMOUNT = 'to_see_contribution_amount';
    public const TO_FILL_CONTRIBUTION_INFORMATIONS = 'to_fill_contribution_informations';
    public const TO_CONTRIBUTION_COMPLETE = 'to_contribution_complete';
}
