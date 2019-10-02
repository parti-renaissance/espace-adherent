<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class DataSurveyAnswerTypeChoice extends Constraint
{
    public $simpleFieldAnswerMessage = 'survey.answer.simple_field.invalid';

    public $multipleChoiceAnswerMessage = 'survey.answer.multiple_choice.invalid';
    public $multipleChoiceAnswerWithTextFieldMessage = 'survey.answer.multiple_choice.with_text_field.invalid';

    public $uniqueChoiceAnswerMessage = 'survey.answer.unique_choice.invalid';
    public $uniqueChoiceAnswerWithTextFieldMessage = 'survey.answer.unique_choice.with_text_field.invalid';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
