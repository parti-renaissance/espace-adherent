<?php

declare(strict_types=1);

namespace App\Validator;

use App\Adherent\MandateTypeEnum;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class MandateZoneTypeValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof MandateZoneType) {
            throw new UnexpectedTypeException($constraint, MandateZoneType::class);
        }

        if (!$value instanceof ElectedRepresentativeAdherentMandate) {
            throw new UnexpectedValueException($value, ElectedRepresentativeAdherentMandate::class);
        }

        $zone = $value->zone;
        $mandateType = $value->mandateType;

        if (null === $zone || !$mandateType) {
            return;
        }

        $zoneConditions = MandateTypeEnum::ZONE_FILTER_BY_MANDATE[$mandateType] ?? null;

        if (null === $zoneConditions) {
            return;
        }

        $allowedTypes = $zoneConditions['types'];
        $allowedCodes = $zoneConditions['codes'] ?? null;

        if (
            !\in_array($zone->getType(), $allowedTypes, true)
            || ($allowedCodes && !\in_array($zone->getCode(), $allowedCodes, true))
        ) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('zone')
                ->addViolation()
            ;
        }
    }
}
