<?php

declare(strict_types=1);

namespace App\Validator;

use App\Validator\Email\DisabledEmailValidator;
use App\Validator\Email\DisposableEmailValidation;
use Egulias\EmailValidator\EmailValidator as EguliasEmailValidator;
use Egulias\EmailValidator\Result\MultipleErrors;
use Egulias\EmailValidator\Result\Reason\DomainAcceptsNoMail;
use Egulias\EmailValidator\Result\Reason\LocalOrReservedDomain;
use Egulias\EmailValidator\Result\Reason\NoDNSRecord;
use Egulias\EmailValidator\Result\Reason\Reason;
use Egulias\EmailValidator\Result\Reason\UnableToGetDNSRecord;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\Extra\SpoofCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\NoRFCWarningsValidation;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class StrictEmailValidator extends ConstraintValidator
{
    public function __construct(private readonly DisabledEmailValidator $disabledEmailValidator)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof StrictEmail) {
            throw new UnexpectedTypeException($constraint, StrictEmail::class);
        }

        if (null === $value) {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $validator = new EguliasEmailValidator();

        $emailValidators = [new NoRFCWarningsValidation()];

        if ($constraint->disposable) {
            $emailValidators[] = new DisposableEmailValidation();
        }

        $emailValidators[] = new SpoofCheckValidation();

        if ($constraint->dnsCheck) {
            $emailValidators[] = new DNSCheckValidation();
        }

        if ($constraint->disabledEmail) {
            $emailValidators[] = $this->disabledEmailValidator;
        }

        if (!$validator->isValid($value, new MultipleValidationWithAnd($emailValidators, MultipleValidationWithAnd::STOP_ON_ERROR))) {
            /** @var MultipleErrors $error */
            $error = $validator->getError();

            foreach ($error->getReasons() as $reason) {
                $reasonLevel = $this->getCodeFromReason($reason, $constraint);
                $this->context
                    ->buildViolation($reasonLevel === $constraint::LEVEL_ERROR ? $constraint->errorMessage : $constraint->warningMessage)
                    ->setParameter('{{ email }}', $value)
                    ->setCause($reasonLevel)
                    ->addViolation()
                ;
            }
        }
    }

    private function getCodeFromReason(Reason $reason, StrictEmail $constraint): string
    {
        $dnsCheckReasonsClass = [
            LocalOrReservedDomain::class,
            UnableToGetDNSRecord::class,
            NoDNSRecord::class,
            DomainAcceptsNoMail::class,
        ];

        foreach ($dnsCheckReasonsClass as $class) {
            if (is_a($reason, $class)) {
                return $constraint::LEVEL_WARNING;
            }
        }

        return $constraint::LEVEL_ERROR;
    }
}
