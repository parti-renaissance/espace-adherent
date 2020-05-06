<?php

namespace App\Validator;

use App\Donation\DonationRequest;
use App\Repository\TransactionRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MaxFiscalYearDonationValidator extends ConstraintValidator
{
    private $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof MaxFiscalYearDonation) {
            throw new UnexpectedTypeException($constraint, MaxFiscalYearDonation::class);
        }

        if (null === $value) {
            return;
        }

        /** @var DonationRequest $donationRequest */
        if (!($donationRequest = $this->context->getObject()) instanceof DonationRequest) {
            throw new UnexpectedTypeException($value, DonationRequest::class);
        }

        if (!$email = $donationRequest->getEmailAddress()) {
            return;
        }

        $totalCurrentAmountInCents = $this->transactionRepository->getTotalAmountInCentsByEmail($email);
        $amountInCents = (int) $value * 100;
        $maxDonationRemainingPossible = $constraint->maxDonationInCents - $totalCurrentAmountInCents;

        if ($maxDonationRemainingPossible < $amountInCents) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameters([
                    '{{ total_current_amount }}' => $totalCurrentAmountInCents / 100,
                    '{{ max_amount_per_fiscal_year }}' => $constraint->maxDonationInCents / 100,
                    '{{ max_donation_remaining_possible }}' => $maxDonationRemainingPossible / 100,
                ])
                ->addViolation()
            ;
        }
    }
}
