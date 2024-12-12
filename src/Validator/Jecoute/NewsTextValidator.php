<?php

namespace App\Validator\Jecoute;

use App\Entity\Jecoute\News;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NewsTextValidator extends ConstraintValidator
{
    /**
     * @param News $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof NewsText) {
            throw new UnexpectedTypeException($constraint, NewsText::class);
        }

        $isEnriched = $value->isEnriched();
        if (null == $value->getText()) {
            $this
                ->context
                ->buildViolation($constraint->messageRequired)
                ->atPath($isEnriched ? 'enrichedText' : 'text')
                ->addViolation()
            ;
        }

        $textLength = \strlen($value->getText());
        if (($textLength > 1000 && !$isEnriched)
            || ($textLength > 10000 && $isEnriched)) {
            $this
                ->context
                ->buildViolation($constraint->messageLength)
                ->setParameter('{{ limit }}', $isEnriched ? $constraint->enrichedTextLength : $constraint->textLength)
                ->atPath($isEnriched ? 'enrichedText' : 'text')
                ->addViolation()
            ;
        }
    }
}
