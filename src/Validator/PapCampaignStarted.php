<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class PapCampaignStarted extends Constraint
{
    public $messageSurvey = 'pap.campaign.not_changeable_survey';
    public $messageVotePlaces = 'pap.campaign.not_changeable_vote_places';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
