<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PapCampaignStarted extends Constraint
{
    public $messageSurvey = 'pap.campaign.not_changeable_survey';
    public $messageVotePlaces = 'pap.campaign.not_changeable_vote_places';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
