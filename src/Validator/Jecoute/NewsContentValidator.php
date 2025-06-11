<?php

namespace App\Validator\Jecoute;

use App\Entity\Jecoute\News;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NewsContentValidator extends ConstraintValidator
{
    /**
     * @param News $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof NewsContent) {
            throw new UnexpectedTypeException($constraint, NewsContent::class);
        }

        if (empty($value->getContent())) {
            $this
                ->context
                ->buildViolation($constraint->messageRequired)
                ->atPath($constraint->path)
                ->addViolation()
            ;
        }

        if (mb_strlen($value->getContent()) > $constraint->contentLength) {
            $this
                ->context
                ->buildViolation($constraint->messageLength)
                ->setParameter('{{ limit }}', $constraint->contentLength)
                ->atPath($constraint->path)
                ->addViolation()
            ;
        }
    }
}
