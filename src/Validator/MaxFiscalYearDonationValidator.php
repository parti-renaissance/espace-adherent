<?php

declare(strict_types=1);

namespace App\Validator;

use App\Donation\Request\DonationRequestInterface;
use App\Repository\TransactionRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class MaxFiscalYearDonationValidator extends ConstraintValidator
{
    private TransactionRepository $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof MaxFiscalYearDonation) {
            throw new UnexpectedTypeException($constraint, MaxFiscalYearDonation::class);
        }

        if (!$value instanceof DonationRequestInterface) {
            throw new UnexpectedValueException($value, DonationRequestInterface::class);
        }

        if (!($email = $value->getEmailAddress()) or !$value->getAmount()) {
            return;
        }

        $totalCurrentAmountInCents = $this->transactionRepository->getTotalAmountInCentsByEmail($email);
        $amountInCents = (int) $value->getAmount() * 100;
        $maxDonationRemainingPossible = $constraint->maxDonationInCents - $totalCurrentAmountInCents;

        if ($maxDonationRemainingPossible < $amountInCents) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameters([
                    '{{ total_current_amount }}' => $totalCurrentAmountInCents / 100,
                    '{{ max_amount_per_fiscal_year }}' => $constraint->maxDonationInCents / 100,
                    '{{ max_donation_remaining_possible }}' => $maxDonationRemainingPossible / 100,
                ])
                ->atPath($constraint->path ?? '')
                ->addViolation()
            ;
        }
    }
}
