<?php

namespace App\Validator;

use App\Repository\IdeasWorkshop\QuestionRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MandatoryQuestionValidator extends ConstraintValidator
{
    private $questionRepository;

    public function __construct(QuestionRepository $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof MandatoryQuestion) {
            throw new UnexpectedTypeException($constraint, MandatoryQuestion::class);
        }

        if (null === $value) {
            return;
        }

        $mandatoryQuestions = [];

        foreach ($value as $answer) {
            if ($answer->getQuestion()->isRequired()) {
                $mandatoryQuestions[] = $answer->getQuestion();
            }
        }

        if (
            array_diff(
                $this->questionRepository->findMandatoryQuestions(),
                $mandatoryQuestions
            )
        ) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
