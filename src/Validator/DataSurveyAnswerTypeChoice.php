<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class DataSurveyAnswerTypeChoice extends Constraint
{
    public $simpleFieldAnswerMessage = 'survey.answer.simple_field.invalid';
    public $multipleChoiceAnswerWithTextFieldMessage = 'survey.answer.multiple_choice.with_text_field.invalid';
    public $uniqueChoiceAnswerWithTextFieldMessage = 'survey.answer.unique_choice.with_text_field.invalid';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
