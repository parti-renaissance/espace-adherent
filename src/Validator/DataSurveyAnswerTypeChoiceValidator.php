<?php

namespace AppBundle\Validator;

use AppBundle\Entity\Jecoute\DataAnswer;
use AppBundle\Jecoute\SurveyQuestionTypeEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DataSurveyAnswerTypeChoiceValidator extends ConstraintValidator
{
    /**
     * @param DataAnswer $answer
     */
    public function validate($answer, Constraint $constraint)
    {
        if (!$constraint instanceof DataSurveyAnswerTypeChoice) {
            throw new UnexpectedTypeException($constraint, DataSurveyAnswerTypeChoice::class);
        }

        if (null === $answer || null === $answer->getSurveyQuestion()) {
            return;
        }

        $surveyQuestion = $answer->getSurveyQuestion();

        switch ($surveyQuestion->getQuestion()->getType()) {
            case SurveyQuestionTypeEnum::SIMPLE_FIELD:
                if (!$answer->getSelectedChoices()->isEmpty()) {
                    $this->processViolation($constraint->simpleFieldAnswerMessage, $surveyQuestion->getId());
                }

                break;
            case SurveyQuestionTypeEnum::UNIQUE_CHOICE_TYPE:
                if (1 !== $answer->getSelectedChoices()->count()) {
                    $this->processViolation($constraint->uniqueChoiceAnswerMessage, $surveyQuestion->getId());
                }

                if ($answer->getTextField()) {
                    $this->processViolation(
                        $constraint->uniqueChoiceAnswerWithTextFieldMessage,
                        $surveyQuestion->getId()
                    );
                }

                break;
            case SurveyQuestionTypeEnum::MULTIPLE_CHOICE_TYPE:
                if ($answer->getSelectedChoices()->isEmpty()) {
                    $this->processViolation($constraint->multipleChoiceAnswerMessage, $surveyQuestion->getId());
                }

                if ($answer->getTextField()) {
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
            ->setParameter('{{ surveyQuestionId }}', $surveyQuestionId)
            ->atPath('surveyQuestion')
            ->addViolation()
        ;
    }
}
