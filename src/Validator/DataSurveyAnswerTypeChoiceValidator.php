<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Jecoute\DataAnswer;
use App\Jecoute\SurveyQuestionTypeEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DataSurveyAnswerTypeChoiceValidator extends ConstraintValidator
{
    /**
     * @param DataAnswer $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof DataSurveyAnswerTypeChoice) {
            throw new UnexpectedTypeException($constraint, DataSurveyAnswerTypeChoice::class);
        }

        if (null === $value || null === $value->getSurveyQuestion()) {
            return;
        }

        $surveyQuestion = $value->getSurveyQuestion();

        switch ($surveyQuestion->getQuestion()->getType()) {
            case SurveyQuestionTypeEnum::SIMPLE_FIELD:
                if (!$value->getSelectedChoices()->isEmpty()) {
                    $this->processViolation($constraint->simpleFieldAnswerMessage, $surveyQuestion->getId());
                }

                break;
            case SurveyQuestionTypeEnum::UNIQUE_CHOICE_TYPE:
                if ($value->getTextField()) {
                    $this->processViolation(
                        $constraint->uniqueChoiceAnswerWithTextFieldMessage,
                        $surveyQuestion->getId()
                    );
                }

                break;
            case SurveyQuestionTypeEnum::MULTIPLE_CHOICE_TYPE:
                if ($value->getTextField()) {
                    $this->processViolation(
                        $constraint->multipleChoiceAnswerWithTextFieldMessage,
                        $surveyQuestion->getId()
                    );
                }

                break;
        }
    }

    public function processViolation(string $message, int $surveyQuestionId): void
    {
        $this
            ->context
            ->buildViolation($message)
            ->setParameter('{{ surveyQuestionId }}', (string) $surveyQuestionId)
            ->atPath('surveyQuestion')
            ->addViolation()
        ;
    }
}
