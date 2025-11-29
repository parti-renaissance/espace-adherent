<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class PapCampaignVotePlaces extends Constraint
{
    public $messageAnotherCampaign = 'Un ou plusieurs bureaux de vote que vous avez choisi sont déjà dans une autre campagne.';
    public $messageNotInManagedZone = 'Un ou plusieurs bureaux de vote ne sont pas dans la zone gérée';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
