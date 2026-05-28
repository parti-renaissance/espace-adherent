<?php

declare(strict_types=1);

namespace App\Validator;

use App\Validator\Email\DisabledEmailValidator;
use App\Validator\Email\DisposableEmailValidation;
use App\Validator\Email\EmailForceableRequest;
use App\Validator\Email\EmailTypoValidation;
use App\Validator\Email\Reason\EmailTypoReason;
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
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class StrictEmailValidator extends ConstraintValidator
{
    public function __construct(
        private readonly DisabledEmailValidator $disabledEmailValidator,
        private readonly LoggerInterface $logger,
    ) {
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

        if ($constraint->typoCheck && !$this->isEmailForcedByContext()) {
            $emailValidators[] = new EmailTypoValidation();
        }

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
                if ($reason instanceof EmailTypoReason) {
                    $this->logger->info('email_typo_suggestion', [
                        'original' => $this->maskEmail($value),
                        'suggestion' => $this->maskEmail($reason->suggestion),
                    ]);

                    $this->context
                        ->buildViolation('Vouliez-vous dire « {{ suggestion }} » ?')
                        ->setParameter('{{ email }}', $value)
                        ->setParameter('{{ suggestion }}', $reason->suggestion)
                        ->setCode('email_typo_suggestion')
                        ->setCause($constraint::LEVEL_ERROR)
                        ->addViolation()
                    ;

                    continue;
                }

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

    protected function getCodeFromReason(Reason $reason, StrictEmail $constraint): string
    {
        // Order matters: UnableToGetDNSRecord extends NoDNSRecord, sub-class must be tested first.

        // Soft DNS issues: we could not verify (reserved TLD, DNS unreachable). Always warnings.
        if ($reason instanceof LocalOrReservedDomain || $reason instanceof UnableToGetDNSRecord) {
            return $constraint::LEVEL_WARNING;
        }

        // Hard DNS failures (no MX/A, mail refused): ERROR only when the caller opted in.
        if ($reason instanceof NoDNSRecord || $reason instanceof DomainAcceptsNoMail) {
            return $constraint->strictDnsErrors ? $constraint::LEVEL_ERROR : $constraint::LEVEL_WARNING;
        }

        return $constraint::LEVEL_ERROR;
    }

    private function isEmailForcedByContext(): bool
    {
        $object = $this->context->getObject();

        return $object instanceof EmailForceableRequest && $object->isEmailForced();
    }

    private function maskEmail(string $email): string
    {
        $atPos = mb_strrpos($email, '@');
        if (false === $atPos || $atPos < 1) {
            return '***';
        }

        return mb_substr($email, 0, 1).'***'.mb_substr($email, $atPos);
    }
}
