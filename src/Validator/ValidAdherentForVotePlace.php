<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidAdherentForVotePlace extends Constraint
{
    public $messageCandidatureNotFound = 'assessor.adherent_association.assessor_request_not_found';
    public $messageWrongVotePlace = 'assessor.adherent_association.wrong_vote_place';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
