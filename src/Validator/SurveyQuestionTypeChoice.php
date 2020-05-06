<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class SurveyQuestionTypeChoice extends Constraint
{
    public $choiceMessage = 'survey.question.choice.invalid';
    public $simpleFieldMessage = 'survey.question.simple_field.invalid';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
