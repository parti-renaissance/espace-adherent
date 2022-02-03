<?php

namespace App\Validator;

use App\Entity\Jecoute\Riposte;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class RiposteOpenGraphValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
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

        $openGraph = $value->getOpenGraph();

        if (
            empty($openGraph)
            || empty($openGraph['url'])
            || empty($openGraph['title'])
            || empty($openGraph['description'])
        ) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->atPath('sourceUrl')
                ->addViolation()
            ;
        }
    }
}
