<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class MergeableCommittees extends Constraint
{
    public $sameCommitteeMessage = 'committee.merge.same_committees';
    public $notApprovedMessage = 'committee.merge.not_approved';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
