<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class UniqueVotePlaceAssessor extends Constraint
{
    public $message = 'assessor.unique_vote_place_association';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
