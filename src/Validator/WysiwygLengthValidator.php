<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class WysiwygLengthValidator extends ConstraintValidator
{
    private const REGEX_TAGS = '/<("[^"]*"|"[^"]*"|[^"">])*>/i';
    private const REGEX_BEGIN_END_STRING_WHITESPACES = '/^\s+|\s+$/';

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof WysiwygLength) {
            throw new UnexpectedTypeException($constraint, WysiwygLength::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!\is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedValueException($value, 'string');
        }

        $stringValue = (string) $value;

        $length = mb_strlen($stringValue, $constraint->charset);

        if (!$invalidCharset = !@mb_check_encoding($stringValue, $constraint->charset)) {
            $stringValueWithoutTags = preg_replace(self::REGEX_TAGS, '', preg_replace(self::REGEX_BEGIN_END_STRING_WHITESPACES, '', $stringValue));
            $length = mb_strlen($stringValueWithoutTags, $constraint->charset);
        }

        if ($invalidCharset) {
            $this->context->buildViolation($constraint->charsetMessage)
                ->setParameter('{{ value }}', $this->formatValue($stringValue))
                ->setParameter('{{ charset }}', $constraint->charset)
                ->setInvalidValue($value)
                ->setCode(WysiwygLength::INVALID_CHARACTERS_ERROR)
                ->addViolation()
            ;

            return;
        }

        if (null !== $constraint->max && $length > $constraint->max) {
            $this->context->buildViolation($constraint->min == $constraint->max ? $constraint->exactMessage : $constraint->maxMessage)
                ->setParameter('{{ value }}', $this->formatValue($stringValue))
                ->setParameter('{{ limit }}', $constraint->max)
                ->setInvalidValue($value)
                ->setPlural((int) $constraint->max)
                ->setCode(WysiwygLength::TOO_LONG_ERROR)
                ->addViolation()
            ;

            return;
        }

        if (null !== $constraint->min && $length < $constraint->min) {
            $this->context->buildViolation($constraint->min == $constraint->max ? $constraint->exactMessage : $constraint->minMessage)
                ->setParameter('{{ value }}', $this->formatValue($stringValue))
                ->setParameter('{{ limit }}', $constraint->min)
                ->setInvalidValue($value)
                ->setPlural((int) $constraint->min)
                ->setCode(WysiwygLength::TOO_SHORT_ERROR)
                ->addViolation()
            ;
        }
    }
}
