<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\CommitteeMembership;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class CommitteeMembershipZoneInScopeZonesValidator extends AbstractZonesInScopeZonesValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (null === $value) {
            return;
        }

        if (!$constraint instanceof CommitteeMembershipZoneInScopeZones) {
            throw new UnexpectedTypeException($constraint, CommitteeMembershipZoneInScopeZones::class);
        }

        if (!$value instanceof CommitteeMembership) {
            throw new UnexpectedValueException($value, CommitteeMembership::class);
        }

        $this->validateZones($value->getAdherent()->getZones()->toArray(), $constraint);
    }
}
