<?php

namespace App\Validator;

use App\Entity\Jecoute\Question;
use App\Jecoute\SurveyQuestionTypeEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class SurveyQuestionTypeChoiceValidator extends ConstraintValidator
{
    /**
     * @param Question $question
     */
    public function validate($question, Constraint $constraint)
    {
        if (!$constraint instanceof SurveyQuestionTypeChoice) {
            throw new UnexpectedTypeException($constraint, SurveyQuestionTypeChoice::class);
        }

        if (null === $question) {
            return;
        }

        if (SurveyQuestionTypeEnum::SIMPLE_FIELD == $question->getType() && !$question->getChoices()->isEmpty()) {
            $this
                ->context
                ->buildViolation($constraint->simpleFieldMessage)
                ->atPath('type')
                ->addViolation()
            ;
        } elseif ($question->isChoiceType() && $question->getChoices()->count() <= 1) {
            $this
                ->context
                ->buildViolation($constraint->choiceMessage)
                ->atPath('type')
                ->addViolation()
            ;
        }
    }
}
