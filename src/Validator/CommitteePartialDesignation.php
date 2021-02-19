<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CommitteePartialDesignation extends Constraint
{
    public $errorCommitteeAlreadyHasActiveDesignation = 'Le comité a déjà une élection en cours.';
    public $errorDesignationTypeMessage = 'Le type de la désignation est invalide.';
    public $errorPoolMessage = 'Le genre est incompatible avec ce comité.';
    public $errorVotersMessage = 'Le comité n\'a aucun membre éligible au vote.';

    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
