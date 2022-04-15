<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PapCampaignVotePlaces extends Constraint
{
    public $messageAnotherCampaign = 'Un ou plusieurs bureaux de vote que vous avez choisi sont déjà dans une autre campagne.';
    public $messageNotInManagedZone = 'Un ou plusieurs bureaux de vote ne sont pas dans la zone gérée';
    public $messageAnotherCampaignByZone = 'Il existe déjà une campagne dans la zone que vous gérez pour cette période.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
