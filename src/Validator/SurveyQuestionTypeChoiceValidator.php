<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Jecoute\Question;
use App\Jecoute\SurveyQuestionTypeEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class SurveyQuestionTypeChoiceValidator extends ConstraintValidator
{
    /**
     * @param Question $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof SurveyQuestionTypeChoice) {
            throw new UnexpectedTypeException($constraint, SurveyQuestionTypeChoice::class);
        }

        if (null === $value) {
            return;
        }

        if (SurveyQuestionTypeEnum::SIMPLE_FIELD == $value->getType() && !$value->getChoices()->isEmpty()) {
            $this
                ->context
                ->buildViolation($constraint->simpleFieldMessage)
                ->atPath('type')
                ->addViolation()
            ;
        } elseif ($value->isChoiceType() && $value->getChoices()->count() <= 1) {
            $this
                ->context
                ->buildViolation($constraint->choiceMessage)
                ->atPath('type')
                ->addViolation()
            ;
        }
    }
}
