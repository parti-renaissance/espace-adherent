<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class CommitteePartialDesignation extends Constraint
{
    public $errorCommitteeAlreadyHasActiveDesignation = 'Le comité a déjà une élection en cours.';
    public $errorCommitteeApprovedAt = 'Le comité a été validé très récemment.';
    public $errorDesignationTypeMessage = 'Le type de la désignation est invalide.';
    public $errorPoolMessage = 'La civilité est incompatible avec ce comité.';
    public $errorVotersMessage = 'Le comité n\'a aucun membre éligible au vote.';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
