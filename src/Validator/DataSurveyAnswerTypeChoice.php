<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class DataSurveyAnswerTypeChoice extends Constraint
{
    public $simpleFieldAnswerMessage = 'survey.answer.simple_field.invalid';

    public $multipleChoiceAnswerWithTextFieldMessage = 'survey.answer.multiple_choice.with_text_field.invalid';

    public $uniqueChoiceAnswerWithTextFieldMessage = 'survey.answer.unique_choice.with_text_field.invalid';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
