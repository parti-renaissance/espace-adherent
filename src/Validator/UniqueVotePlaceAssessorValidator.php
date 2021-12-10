<?php

namespace App\Validator;

use App\Assessor\AssessorRoleAssociationValueObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UniqueVotePlaceAssessorValidator extends ConstraintValidator
{
    public function validate($collection, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueVotePlaceAssessor) {
            throw new UnexpectedTypeException($constraint, UniqueVotePlaceAssessor::class);
        }

        $cacheOfAlreadyChecked = [];

        foreach ((array) $collection as $index => $object) {
            if (!$object instanceof AssessorRoleAssociationValueObject) {
                throw new UnexpectedValueException($object, AssessorRoleAssociationValueObject::class);
            }

            if (!$adherent = $object->getAdherent()) {
                continue;
            }

            if (
                ($adherent->isAssessor() && !$adherent->getAssessorRole()->getVotePlace()->equals($object->getVotePlace()))
                || \in_array($adherent->getId(), $cacheOfAlreadyChecked, true)
            ) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->atPath("[${index}].adherent")
                    ->addViolation()
                ;

                return;
            }

            $cacheOfAlreadyChecked[] = $adherent->getId();
        }
    }
}
