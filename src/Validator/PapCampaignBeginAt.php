<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PapCampaignBeginAt extends Constraint
{
    public $message = 'pap.campaign.noteditable_start_date';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
