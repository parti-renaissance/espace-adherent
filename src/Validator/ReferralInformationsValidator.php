<?php

namespace App\Validator;

use App\Entity\Referral;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ReferralInformationsValidator extends ConstraintValidator
{
    private const PREREGISTRATION_FIELDS = [
        'civility',
        'lastName',
        'postAddress',
        'nationality',
    ];

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ReferralInformations) {
            throw new UnexpectedTypeException($constraint, ReferralInformations::class);
        }

        if (!$value instanceof Referral) {
            return;
        }

        $filledCount = 0;
        foreach (self::PREREGISTRATION_FIELDS as $field) {
            if ('postAddress' === $field) {
                $postAddress = $value->getPostAddress();

                if ($postAddress && !$postAddress->isEmpty()) {
                    ++$filledCount;
                }

                continue;
            }

            if (!empty($value->$field)) {
                ++$filledCount;
            }
        }

        if (0 !== $filledCount && $filledCount < \count(self::PREREGISTRATION_FIELDS)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
