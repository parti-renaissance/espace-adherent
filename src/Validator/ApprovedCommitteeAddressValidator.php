<?php

namespace App\Validator;

use App\Committee\CommitteeCommand;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ApprovedCommitteeAddressValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ApprovedCommitteeAddress) {
            throw new UnexpectedTypeException($constraint, ApprovedCommitteeAddress::class);
        }

        if (!$value instanceof CommitteeCommand) {
            throw new UnexpectedValueException($value, CommitteeCommand::class);
        }

        $committee = $value->getCommittee();
        if (!$committee || !$committee->isApproved()) {
            return;
        }

        $address = $value->getCommittee()->getPostAddress();
        $newAddress = $value->getAddress();

        if ($newAddress->getCityName() !== $address->getCityName()
            || $newAddress->getPostalCode() !== $address->getPostalCode()
            || $newAddress->getCountry() !== $address->getCountry()) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->atPath($constraint->errorPath)
                ->addViolation()
            ;
        }
    }
}
