<?php

namespace App\Validator\TerritorialCouncil;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TerritorialCouncilDesignation extends Constraint
{
    public $messageVoteStartDateTooClose = 'La convocation doit avoir lieu au minimum 7 jours avant la tenue du vote.';
    public $messageVoteEndDateInvalid = 'La durée du vote doit être comprise entre 5h et 7 jours.';
    public $messageVoteStartDateInvalid = 'La date de début du vote n\'est pas le même jour que la date du Conseil territorial';
    public $messageMeetingStartDateTooFarAway = 'La date de début de la réunion ne doit pas dépasser le {{date}}';
    public $messageMeetingEndDateInvalid = 'La durée de la réunion ne doit pas dépasser 12 heures.';
    public $messageAddressEmpty = 'L\'adresse ne doit pas être vide.';
    public $messageUrlEmpty = 'L\'URL de la réunion ne doit pas être vide.';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
