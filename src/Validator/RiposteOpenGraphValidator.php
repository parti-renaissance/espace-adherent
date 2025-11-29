<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Jecoute\Riposte;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class RiposteOpenGraphValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof RiposteOpenGraph) {
            throw new UnexpectedTypeException($constraint, RiposteOpenGraph::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof Riposte) {
            throw new UnexpectedValueException($value, Riposte::class);
        }

        if (!$value->getSourceUrl()) {
            return;
        }

        if (empty($value->getOpenGraph())) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->atPath('sourceUrl')
                ->addViolation()
            ;
        }
    }
}
