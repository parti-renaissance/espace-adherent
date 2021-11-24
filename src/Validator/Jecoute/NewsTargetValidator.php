<?php

namespace App\Validator\Jecoute;

use App\Entity\Jecoute\News;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NewsTargetValidator extends ConstraintValidator
{
    /**
     * @param News $value
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof NewsTarget) {
            throw new UnexpectedTypeException($constraint, NewsTarget::class);
        }

        if (!($value->isGlobal() xor $value->getZone())) {
            $this
                ->context
                ->buildViolation($constraint->undefinedTarget)
                ->addViolation()
            ;
        }
    }
}
